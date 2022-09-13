<?php

namespace Amp\Parallel\Worker;

/**
 * Interface for factories used to create new workers.
 */
interface WorkerFactory
{
    /**
     * Creates a new worker instance.
     *
     * @return Worker The newly created worker.
     */
    public function create(): Worker;
}
