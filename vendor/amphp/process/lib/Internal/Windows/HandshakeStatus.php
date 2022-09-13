<?php

namespace Amp\Process\Internal\Windows;

/**
 * @internal
 * @codeCoverageIgnore Windows only.
 */
final class HandshakeStatus
{
    const SUCCESS = 0;
    const SIGNAL_UNEXPECTED = 0x01;
    const INVALID_STREAM_ID = 0x02;
    const INVALID_PROCESS_ID = 0x03;
    const DUPLICATE_STREAM_ID = 0x04;
    const INVALID_CLIENT_TOKEN = 0x05;

    private function __construct()
    {
        // empty to prevent instances of this class
    }
}
