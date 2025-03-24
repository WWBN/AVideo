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

use BadMethodCallException;
use ElephantIO\Engine\EngineInterface;
use Exception;

class UnsupportedActionException extends BadMethodCallException
{
    public function __construct(EngineInterface $engine, $action, ?Exception $previous = null)
    {
        parent::__construct(
            \sprintf('The action "%s" is not supported by the engine "%s"', $engine->getName(), $action),
            0,
            $previous
        );
    }
}
