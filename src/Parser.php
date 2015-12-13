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
 * @version     0.1.0
 * @author      Miles Croxford <hello@milescroxford.com>
 * @copyright   Copyright (c) Miles Croxford <hello@milescroxford.com>
 * @license     http://github.com/m1/env/blob/master/LICENSE.md
 * @link        http://github.com/m1/env/blob/master/README.md Documentation
 */

namespace M1\Env;

use M1\Env\Exception\ParseException;

/**
 * The .env parser
 *
 * @since 0.1.0
 */
class Parser
{
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
     * The .env to parse
     *
     * @var string $file
     */
    private $file;

    /**
     * If to throw ParseException in the .env
     *
     * @var bool $origin_exception
     */
    private $origin_exception;

    /**
     * The parser constructor
     *
     * @param string $file The .env to parse
     * @param bool   $origin_exception  Whether or not to throw ParseException in the .env
     */
    public function __construct($file, $origin_exception = false)
    {
        $this->file = $file;
        $this->origin_exception = $origin_exception;
    }

    /**
     * Opens the .env, parses it then returns the contents
     *
     * @return array The .env contents
     */
    public function parse()
    {
        $raw_content = file($this->file, FILE_IGNORE_NEW_LINES);

        return $this->parseContent($raw_content);
    }

    /**
     * Parses the .env line by line
     *
     * @param array $raw_content The raw content of the file
     *
     * @throws \M1\Env\Exception\ParseException If the file does not have a key=value structure
     *
     * @return array The .env contents
     */
    public function parseContent(array $raw_content)
    {
        if (!$raw_content) {
            return array();
        }

        $lines = array();
        $line_num = 0;

        foreach ($raw_content as $raw_line) {
            $line_num++;

            if ($this->startsWith('#', $raw_line) || !$raw_line) {
                continue;
            }
            
            $key_value = explode("=", $raw_line, 2);

            if (empty($key_value)) {
                continue;
            } elseif (count($key_value) !== 2) {
                throw new ParseException(
                    'You must have a key = value',
                    $this->origin_exception,
                    $this->file,
                    $raw_line,
                    $line_num
                );
            }

            $key = $this->parseKey($key_value[0], $line_num);

            if (is_string($key)) {
                $lines[$key] = $this->parseValue($key_value[1], $lines, $line_num);
            }
        }

        return $lines;
    }

    /**
     * Parses a .env key
     *
     * @param string $key      The key string
     * @param int    $line_num The line num of the key
     *
     * @throws \M1\Env\Exception\ParseException If key contains a character that isn't alphanumeric or a _
     *
     * @return string|false The parsed key, or false if the key is a comment
     */
    private function parseKey($key, $line_num)
    {
        $key = trim($key);

        if ($this->startsWith('#', $key)) {
            return false;
        }

        if (!ctype_alnum(str_replace('_', '', $key))) {
            throw new ParseException(
                sprintf('Key can only contain alphanumeric and underscores: %s', $key),
                $this->origin_exception,
                $this->file,
                $key,
                $line_num
            );
        }

        return $key;
    }

    /**
     * Parses a .env value
     *
     * @param string $value    The value to parse
     * @param array  $lines    The array of already parsed lines
     * @param int    $line_num The line num of the value
     *
     * @return string|null The parsed key, or null if the key is a comment
     */
    private function parseValue($value, $lines, $line_num)
    {
        $value = trim($value);

        if ($this->startsWith('#', $value)) {
            return null;
        }

        if ($this->isString($value)) {
            $value = $this->parseString($value, $line_num);
            $value = $this->parseVariables($value, $lines, $line_num, true);

        } else {
            $value = $this->stripComments($value);

            if ($this->isBool($value)) {
                $value = $this->parseBool($value);

            } elseif ($this->isNumber($value)) {
                $value = $this->parseNumber($value);

            } elseif ($this->isNull($value)) {
                $value = null;

            } else {
                $value = $this->parseUnquotedString($value);
                $value = $this->parseVariables($value, $lines, $line_num);

            }
        }

        return $value;
    }

    /**
     * Parses a .env variable
     *
     * @param string $value         The value to parse
     * @param array  $lines         The array of already parsed lines
     * @param int    $line_num      The line num of the value
     * @param bool   $quoted_string Is the value in a quoted string
     *
     * @throws \M1\Env\Exception\ParseException If the variable hasn't been defined
     *
     * @return string The parsed value
     */
    private function parseVariables($value, $lines, $line_num, $quoted_string = false)
    {
        preg_match_all('/'.self::REGEX_ENV_VARIABLE.'/', $value, $matches);

        if (isset($matches[0]) && !empty($matches[0])) {
            $replacements = array();

            for ($i = 0; $i <= (count($matches[0]) - 1); $i++) {
                $variable_name = $matches[1][$i];
                $str_to_replace = $matches[0][$i];

                if (!isset($lines[$variable_name])) {
                    throw new ParseException(
                        sprintf('Variable has not been defined: %s', $variable_name),
                        $this->origin_exception,
                        $this->file,
                        $value,
                        $line_num
                    );
                }

                $replacement = $lines[$variable_name];

                if (count($matches[0]) === 1 &&
                    $value === $str_to_replace &&
                    !$quoted_string
                ) {
                    return $replacement;
                } elseif (is_bool($replacement) &&
                    ($quoted_string || count($matches[0] > 2))
                ) {
                    $replacement = ($replacement) ? 'true' : 'false';
                }

                $replacements[$str_to_replace] = $replacement;
            }

            if (!empty($replacements)) {
                $value = strtr($value, $replacements);
            }
        }

        return $value;
    }

    /**
     * Parses a .env string
     *
     * @param string $value    The value to parse
     * @param int    $line_num The line num of the value
     *
     * @throws \M1\Env\Exception\ParseException If the string has a missing end quote
     *
     * @return string The parsed string
     */
    private function parseString($value, $line_num)
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
                $line_num
            );
        };

        $value = trim($matches[0], $symbol);
        $value = strtr($value, self::$character_map);

        return $value;
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

        return $value;
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
                return true;
            case 'false':
            case 'no':
            default:
                return false;
        }
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

    /**
     * Returns if value is a string
     *
     * @param string $value The value to test
     *
     * @return bool Is a value a string
     */
    private function isString($value)
    {
        return $this->startsWith('\'', $value) || $this->startsWith('"', $value);
    }

    /**
     * Returns if value is a bool
     *
     * @param string $value The value to test
     *
     * @return bool Is a value a bool
     */
    private function isBool($value)
    {
        return in_array(strtolower($value), self::$bool_variants);
    }

    /**
     * Returns if value is number
     *
     * @param string $value The value to test
     *
     * @return bool Is a value a number
     */
    private function isNumber($value)
    {
        return is_numeric($value);
    }

    /**
     * Returns if value is null
     *
     * @param string $value The value to test
     *
     * @return bool Is a value null
     */
    private function isNull($value)
    {
        return $value === 'null';
    }

    /**
     * Returns if value starts with a value
     *
     * @param string $value The value to search for
     * @param string $line  The line to test
     *
     * @return bool Returns if the line starts with value
     */
    private function startsWith($string, $line)
    {
        return $string === "" || strrpos($line, $string, -strlen($line)) !== false;
    }
}
