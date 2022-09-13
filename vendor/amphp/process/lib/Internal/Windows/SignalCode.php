<?php

namespace Amp\Process\Internal\Windows;

/**
 * @internal
 * @codeCoverageIgnore Windows only.
 */
final class SignalCode
{
    const HANDSHAKE = 0x01;
    const HANDSHAKE_ACK = 0x02;
    const CHILD_PID = 0x03;
    const EXIT_CODE = 0x04;

    private function __construct()
    {
        // empty to prevent instances of this class
    }
}
