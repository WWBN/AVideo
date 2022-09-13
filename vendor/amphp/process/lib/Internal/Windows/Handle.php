<?php

namespace Amp\Process\Internal\Windows;

use Amp\Deferred;
use Amp\Process\Internal\ProcessHandle;

/**
 * @internal
 * @codeCoverageIgnore Windows only.
 */
final class Handle extends ProcessHandle
{
    public function __construct()
    {
        $this->joinDeferred = new Deferred;
        $this->pidDeferred = new Deferred;
    }

    /** @var Deferred */
    public $joinDeferred;

    /** @var string */
    public $exitCodeWatcher;

    /** @var bool */
    public $exitCodeRequested = false;

    /** @var resource */
    public $proc;

    /** @var int */
    public $wrapperPid;

    /** @var resource */
    public $wrapperStderrPipe;

    /** @var resource[] */
    public $sockets = [];

    /** @var Deferred[] */
    public $stdioDeferreds;

    /** @var string */
    public $childPidWatcher;

    /** @var string */
    public $connectTimeoutWatcher;

    /** @var string[] */
    public $securityTokens;
}
