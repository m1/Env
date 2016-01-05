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

/**
 * Env core class
 *
 * @since 0.1.0
 */
class Env
{
    /**
     * The .env contents
     *
     * @var array $contents
     */
    private $contents = array();

    /**
     * Creates a new instance of Env
     *
     * @param string $file              The .env to parse
     * @param bool   $origin_exception  Whether or not to throw ParseException in the .env
     *
     * @throws \InvalidArgumentException If the file does not exist or is not readable
     */
    public function __construct($file, $origin_exception = false)
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new \InvalidArgumentException(sprintf('%s is not a file or readable', $file));
        }

        $parser = new Parser($file, $origin_exception);

        $this->contents = $parser->parse();
    }

    /**
     * Parses the .env and returns the contents statically
     *
     * @param string $file              The .env to parse
     * @param bool   $origin_exception  Whether or not to throw ParseException in the .env
     *
     * @return array The .env contents
     */
    public static function parse($file, $origin_exception = false)
    {
        $env = new Env($file, $origin_exception);

        return $env->getContents();
    }

    /**
     * Returns the contents of the .env
     *
     * @return array The .env contents
     */
    public function getContents()
    {
        return $this->contents;
    }
}
