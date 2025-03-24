<?php

/**
 * This file is part of the Elephant.io package
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright Wisembly
 * @license   http://www.opensource.org/licenses/MIT-License MIT License
 */

namespace ElephantIO\Engine;

use \ElephantIO\Util;

/**
 * Arguments to emit to server.
 * 
 * To create any arguments to server:
 * 
 * ```php
 * $args = new \ElephantIO\Engine\Argument('first', 2, ['data' => 3]);
 * ```
 *
 * @author Toha <tohenk@yahoo.com>
 */
class Argument
{
    protected $args = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->args = func_get_args();
    }

    /**
     * Get all arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->args;
    }

    public function __toString()
    {
        return Util::toStr($this->args);
    }

    /**
     * Create argument from array.
     *
     * @param array $array
     * @return \ElephantIO\Engine\Argument
     */
    public static function from($array)
    {
        return null !== $array ? new self((array) $array) : new self();
    }
}
