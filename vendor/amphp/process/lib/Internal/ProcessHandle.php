<?php

namespace Amp\Process\Internal;

use Amp\Deferred;
use Amp\Process\ProcessInputStream;
use Amp\Process\ProcessOutputStream;
use Amp\Struct;

abstract class ProcessHandle
{
    use Struct;

    /** @var ProcessOutputStream */
    public $stdin;

    /** @var ProcessInputStream */
    public $stdout;

    /** @var ProcessInputStream */
    public $stderr;

    /** @var Deferred */
    public $pidDeferred;

    /** @var int */
    public $status = ProcessStatus::STARTING;
}
