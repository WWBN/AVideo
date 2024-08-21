<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP;

interface TOTPInterface extends OTPInterface
{
    /**
     * Create a new TOTP object.
     *
     * If the secret is null, a random 64 bytes secret will be generated.
     */
    public static function create(?string $secret = null, int $period = 30, string $digest = 'sha1', int $digits = 6): self;

    /**
     * Return the TOTP at the current time.
     */
    public function now(): string;

    /**
     * Get the period of time for OTP generation (a non-null positive integer, in second).
     */
    public function getPeriod(): int;

    public function getEpoch(): int;
}
