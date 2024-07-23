<?php

declare(strict_types=1);

namespace Bunny\Storage;

class InvalidRegionException extends Exception
{
    public function __construct(int $code = 0, ?\Exception $previous = null)
    {
        parent::__construct('Invalid storage region', $code, $previous);
    }
}
