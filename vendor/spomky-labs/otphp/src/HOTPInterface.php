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

interface HOTPInterface extends OTPInterface
{
    /**
     * The initial counter (a positive integer).
     */
    public function getCounter(): int;

    /**
     * Create a new TOTP object.
     *
     * If the secret is null, a random 64 bytes secret will be generated.
     */
    public static function create(?string $secret = null, int $counter = 0, string $digest = 'sha1', int $digits = 6): self;
}
