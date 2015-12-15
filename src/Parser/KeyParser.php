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

/**
 * The key parser for Env
 *
 * @since 0.2.0
 */
class KeyParser extends AbstractParser
{

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
    public function parse($key, $line_num)
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
}