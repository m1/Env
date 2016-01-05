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
 * @version     1.1.0
 * @author      Miles Croxford <hello@milescroxford.com>
 * @copyright   Copyright (c) Miles Croxford <hello@milescroxford.com>
 * @license     http://github.com/m1/env/blob/master/LICENSE.md
 * @link        http://github.com/m1/env/blob/master/README.md Documentation
 */

namespace M1\Env;

use M1\Env\Exception\ParseException;
use M1\Env\Parser\ValueParser;
use M1\Env\Parser\KeyParser;
use M1\Env\Traits\ValueCheckTrait;

/**
 * The .env parser
 *
 * @since 0.1.0
 */
class Parser
{
    /**
     * The trait for checking types
     */
    use ValueCheckTrait;

    /**
     * The .env to parse
     *
     * @var string $file
     */
    public $file;

    /**
     * The Env key parser
     *
     * @var \M1\Env\Parser\KeyParser $key_parser
     */
    private $key_parser;

    /**
     * The line num of the current value
     *
     * @var int $line_num
     */
    public $line_num;

    /**
     * The current parsed values/lines
     *
     * @var array $lines
     */
    public $lines;

    /**
     * If to throw ParseException in the .env
     *
     * @var bool $origin_exception
     */
    public $origin_exception;

    /**
     * The Env value parser
     *
     * @var \M1\Env\Parser\ValueParser $value_parser
     */
    public $value_parser;

    /**
     * The parser constructor
     *
     * @param string $file             The .env to parse
     * @param bool   $origin_exception Whether or not to throw ParseException in the .env
     */
    public function __construct($file, $origin_exception = false)
    {
        $this->file = $file;
        $this->origin_exception = $origin_exception;
        $this->key_parser = new KeyParser($this);
        $this->value_parser = new ValueParser($this);
    }

    /**
     * Opens the .env, parses it then returns the contents
     *
     * @return array The .env contents
     */
    public function parse()
    {
        $raw_content = file($this->file, FILE_IGNORE_NEW_LINES);

        if (empty($raw_content)) {
            return array();
        }

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
        $this->lines = array();
        $this->line_num = 0;

        foreach ($raw_content as $raw_line) {
            $this->line_num++;

            if ($this->startsWith('#', $raw_line) || !$raw_line) {
                continue;
            }

            $this->parseLine($raw_line);
        }

        return $this->lines;
    }

    /**
     * Parses a line of the .env
     *
     * @param string $raw_line The raw content of the line
     *
     * @return array The parsed lines
     */
    private function parseLine($raw_line)
    {
        list($key, $value) = $this->parseKeyValue($raw_line);

        $key = $this->key_parser->parse($key);

        if (!is_string($key)) {
            return;
        }

        $this->lines[$key] = $this->value_parser->parse($value);
    }

    /**
     * Gets the key = value items from the line
     *
     * @param string $raw_line The raw content of the line
     *
     * @throws \M1\Env\Exception\ParseException If the line does not have a key=value structure
     *
     * @return array The parsed lines
     */
    private function parseKeyValue($raw_line)
    {
        $key_value = explode("=", $raw_line, 2);

        if (count($key_value) !== 2) {
            throw new ParseException(
                'You must have a key = value',
                $this->origin_exception,
                $this->file,
                $raw_line,
                $this->line_num
            );
        }

        return $key_value;
    }
}
