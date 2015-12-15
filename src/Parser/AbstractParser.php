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

/**
 * The abstract parser for Env
 *
 * @since 0.2.0
 */
abstract class AbstractParser
{
    /**
     * The .env to parse
     *
     * @var string $file
     */
    protected $file;

    /**
     * If to throw ParseException in the .env
     *
     * @var bool $origin_exception
     */
    protected $origin_exception;

    /**
     * The abstract parser constructor for Env
     *
     * @param string $file           The file to use with Env
     * @param bool $origin_exception If to throw exceptions in the origin file
     */
    public function __construct($file, $origin_exception)
    {
        $this->file = $file;
        $this->origin_exception = $origin_exception;
    }

    /**
     * Returns if value starts with a value
     *
     * @param string $string The value to search for
     * @param string $line   The line to test
     *
     * @return bool Returns if the line starts with value
     */
    protected function startsWith($string, $line)
    {
        return $string === "" || strrpos($line, $string, -strlen($line)) !== false;
    }
}