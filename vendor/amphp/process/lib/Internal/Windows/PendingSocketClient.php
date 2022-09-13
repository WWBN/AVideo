<?php

namespace Amp\Process\Internal\Windows;

use Amp\Struct;

/**
 * @internal
 * @codeCoverageIgnore Windows only.
 */
final class PendingSocketClient
{
    use Struct;

    public $readWatcher;
    public $timeoutWatcher;
    public $receivedDataBuffer = '';
    public $pid;
    public $streamId;
}
