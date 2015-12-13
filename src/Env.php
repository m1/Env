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
     * The .env to parse
     *
     * @var string $file
     */
    private $file;

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

        $setup = $this->setup();
        $parser = new Parser($file, $origin_exception);

        $this->file = $file;
        $this->contents = $parser->parse();

        $this->cleanUp($setup);
    }

    /**
     * Sets up the environment for Env
     *
     * @return array The array of changes
     */
    private function setup()
    {
        $setup = array(
            'auto_detect_line_endings' => ini_get('auto_detect_line_endings')
        );

        ini_set('auto_detect_line_endings', '1');

        return $setup;
    }

    /**
     * Sets up the environment for Env
     *
     * @param array $setup The setup variables to change back
     */
    private function cleanUp(array $setup)
    {
        ini_set('auto_detect_line_endings', $setup['auto_detect_line_endings']);
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
