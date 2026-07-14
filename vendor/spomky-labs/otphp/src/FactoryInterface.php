<?php

declare(strict_types=1);

namespace OTPHP;

use Psr\Clock\ClockInterface;

interface FactoryInterface
{
    /**
     * This method is the unique public method of the class. It can load a provisioning Uri and convert it into an OTP
     * object.
     *
     * @param non-empty-string $uri
     */
    public static function loadFromProvisioningUri(string $uri, ?ClockInterface $clock = null): OTPInterface;
}
