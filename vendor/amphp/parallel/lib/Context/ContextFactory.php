<?php

namespace Amp\Parallel\Context;

use Amp\Promise;

interface ContextFactory
{
    /**
     * Creates a new execution context.
     *
     * @param string|string[] $script Path to PHP script or array with first element as path and following elements options
     *     to the PHP script (e.g.: ['bin/worker', 'Option1Value', 'Option2Value'].
     *
     * @return Context
     */
    public function create($script): Context;

    /**
     * Creates and starts a new execution context.
     *
     * @param string|string[] $script Path to PHP script or array with first element as path and following elements options
     *     to the PHP script (e.g.: ['bin/worker', 'Option1Value', 'Option2Value'].
     *
     * @return Promise<Context>
     */
    public function run($script): Promise;
}
