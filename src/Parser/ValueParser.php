<?php

/**
 * This file is part of the m1\env library
 *
 * (c) m1 <hello@milescroxford.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package     m1/env
 * @version     0.2.0
 * @author      Miles Croxford <hello@milescroxford.com>
 * @copyright   Copyright (c) Miles Croxford <hello@milescroxford.com>
 * @license     http://github.com/m1/env/blob/master/LICENSE.md
 * @link        http://github.com/m1/env/blob/master/README.md Documentation
 */

namespace M1\Env\Parser;

use M1\Env\Exception\ParseException;
use M1\Env\Traits\ValueTypeCheckable;

/**
 * The value parser for Env
 *
 * @since 0.2.0
 */
class ValueParser extends AbstractParser
{
    /**
     * The trait for checking types
     */
    use ValueTypeCheckable;

    /**
     * The regex to get variables '$(VARIABLE)' in .env
     * Unescaped: ${(.*?)}
     *
     * @var string REGEX_ENV_VARIABLE
     */
    const REGEX_ENV_VARIABLE = '\\${(.*?)}';

    /**
     * The regex to get the content between double quote (") strings, ignoring escaped quotes.
     * Unescaped: "(?:[^"\\]*(?:\\.)?)*"
     *
     * @var string REGEX_QUOTE_DOUBLE_STRING
     */
    const REGEX_QUOTE_DOUBLE_STRING = '"(?:[^\"\\\\]*(?:\\\\.)?)*\"';

    /**
     * The regex to get the content between single quote (') strings, ignoring escaped quotes
     * Unescaped: '(?:[^'\\]*(?:\\.)?)*'
     *
     * @var string REGEX_QUOTE_SINGLE_STRING
     */
    const REGEX_QUOTE_SINGLE_STRING = "'(?:[^'\\\\]*(?:\\\\.)?)*'";

    /**
     * The bool variants available in .env
     *
     * @var array $bool_variants
     */
    private static $bool_variants = array(
        'true', 'false', 'yes', 'no'
    );

    /**
     * The map to convert escaped characters into real characters
     *
     * @var array $character_map
     */
    private static $character_map = array(
        "\\n" => "\n",
        "\\\"" => "\"",
        '\\\'' => "'",
        '\\t' => "\t"
    );

    /**
     * The line num of the current value
     *
     * @var int $line_num
     */
    private $line_num;

    /**
     * The current parsed values/lines
     *
     * @var array $lines
     */
    private $lines;

    /**
     * Parses a .env value
     *
     * @param string $value    The value to parse
     * @param array  $lines    The array of already parsed lines
     * @param int    $line_num The line num of the value
     *
     * @return string|null The parsed key, or null if the key is a comment
     */
    public function parse($value, $lines, $line_num)
    {
        $this->lines = $lines;
        $this->line_num = $line_num;

        $value = trim($value);

        if ($this->startsWith('#', $value)) {
            return null;
        }

        return $this->parseValue($value);
    }

    /**
     * Parses a .env value
     *
     * @param string $value The value to parse
     *
     * @return string|null The parsed value, or null if the value is null
     */
    private function parseValue($value)
    {
        $types = array('string', 'bool', 'number', 'null');

        foreach ($types as $type) {
            $parsed_value = $value;

            if ($type !== 'string') {
                $parsed_value = $this->stripComments($value);
            }

            $is_function = sprintf('is%s', ucfirst($type));
            $parse_function = sprintf('parse%s', ucfirst($type));
            
            if ($this->$is_function($parsed_value)) {
                return $this->$parse_function($parsed_value);
            }
        }

        return $this->parseUnquotedString($parsed_value);
    }

    /**
     * Parses a .env variable
     *
     * @param string $value         The value to parse
     * @param bool   $quoted_string Is the value in a quoted string
     *
     * @return string The parsed value
     */
    private function parseVariables($value, $quoted_string = false)
    {
        $matches = $this->fetchVariableMatches($value);

        if (is_array($matches)) {
            $replacements = array();

            if ($this->isVariableClone($value, $matches, $quoted_string)) {
                return $this->fetchVariable($value, $matches[1][0], $matches, $quoted_string);
            }

            for ($i = 0; $i <= (count($matches[0]) - 1); $i++) {
                $replacement = $this->fetchVariable($value, $matches[1][$i], $matches, $quoted_string);
                $replacements[$matches[0][$i]] = $replacement;
            }

            if (!empty($replacements)) {
                $value = strtr($value, $replacements);
            }
        }

        return $value;
    }

    /**
     * Get variable matches inside a string
     *
     * @param string $value The value to parse
     *
     * @return array The variable matches
     */
    private function fetchVariableMatches($value)
    {
        preg_match_all('/'.self::REGEX_ENV_VARIABLE.'/', $value, $matches);

        if (!is_array($matches) || !isset($matches[0]) || empty($matches[0])) {
            return false;
        }

        return $matches;
    }

    /**
     * Parses a .env variable
     *
     * @param string $value         The value to parse
     * @param string $variable_name The variable name to get
     * @param array  $matches       The matches of the variables
     * @param bool   $quoted_string Is the value in a quoted string
     *
     * @throws \M1\Env\Exception\ParseException If the variable can not be found
     *
     * @return string The parsed value
     */
    private function fetchVariable($value, $variable_name, $matches, $quoted_string)
    {

        if (!isset($this->lines[$variable_name])) {
            throw new ParseException(
                sprintf('Variable has not been defined: %s', $variable_name),
                $this->origin_exception,
                $this->file,
                $value,
                $this->line_num
            );
        }

        $replacement = $this->lines[$variable_name];

        if (is_bool($replacement) &&
            ($quoted_string || count($matches[0]) >= 2)) {
            $replacement = ($replacement) ? 'true' : 'false';
        }

        return $replacement;
    }

    /**
     * Parses a .env string
     *
     * @param string $value    The value to parse
     *
     * @throws \M1\Env\Exception\ParseException If the string has a missing end quote
     *
     * @return string The parsed string
     */
    private function parseString($value)
    {
        if ($this->startsWith('\'', $value)) {
            $regex =  self::REGEX_QUOTE_SINGLE_STRING;
            $symbol = "'";
        } else {
            $regex = self::REGEX_QUOTE_DOUBLE_STRING;
            $symbol = '"';
        }

        if (!preg_match('/'.$regex.'/', $value, $matches)) {
            throw new ParseException(
                sprintf('Missing end %s quote', $symbol),
                $this->origin_exception,
                $this->file,
                $value,
                $this->line_num
            );
        }

        $value = trim($matches[0], $symbol);
        $value = strtr($value, self::$character_map);

        return $this->parseVariables($value, true);
    }

    /**
     * Parses a .env null value
     *
     * @param string $value The value to parse
     *
     * @return null Null value
     */
    private function parseNull($value)
    {
        return null;
    }

    /**
     * Parses a .env unquoted string
     *
     * @param string $value The value to parse
     *
     * @return string The parsed string
     */
    private function parseUnquotedString($value)
    {
        if ($value == "") {
            return null;
        }

        return $this->parseVariables($value);
    }

    /**
     * Parses a .env bool
     *
     * @param string $value The value to parse
     *
     * @return bool The parsed bool
     */
    private function parseBool($value)
    {
        switch (strtolower($value)) {
            case 'true':
            case 'yes':
                $value = true;
                break;
            case 'false':
            case 'no':
            default:
                $value = false;
                break;
        }

        return $value;
    }

    /**
     * Parses a .env number
     *
     * @param string $value The value to parse
     *
     * @return int|float The parsed bool
     */
    private function parseNumber($value)
    {
        if (strpos($value, '.') !== false) {
            return (float) $value;
        }

        return (int) $value;
    }

    /**
     * Strips comments from a value
     *
     * @param string $value The value to strip comments from
     *
     * @return string value
     */
    private function stripComments($value)
    {
        return trim(explode("#", $value, 2)[0]);
    }
}