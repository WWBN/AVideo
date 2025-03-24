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

namespace ElephantIO\Exception;

use Exception;
use InvalidArgumentException;

class MalformedUrlException extends InvalidArgumentException
{
    public function __construct($url, ?Exception $previous = null)
    {
        parent::__construct(\sprintf('The url "%s" seems to be malformed', $url), 0, $previous);
    }
}
