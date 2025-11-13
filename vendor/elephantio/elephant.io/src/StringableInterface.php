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

namespace ElephantIO;

/**
 * Stringable interface.
 *
 * @author Toha <tohenk@yahoo.com>
 */
interface StringableInterface
{
    /**
     * Cast object to string.
     *
     * @return string
     */
    public function __toString();
}
