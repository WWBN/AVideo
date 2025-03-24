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
use RuntimeException;

class SocketException extends RuntimeException
{
    public function __construct($errno, $error, ?Exception $previous = null)
    {
        parent::__construct(
            \sprintf(
                'There was an error while attempting to open a connection to the socket (Err #%d : %s)',
                $errno,
                $error
            ),
            $errno,
            $previous
        );
    }
}
