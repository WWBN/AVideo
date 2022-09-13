<?php

namespace Amp\Process\Internal;

final class ProcessStatus
{
    const STARTING = 0;
    const RUNNING = 1;
    const ENDED = 2;

    private function __construct()
    {
        // empty to prevent instances of this class
    }
}
