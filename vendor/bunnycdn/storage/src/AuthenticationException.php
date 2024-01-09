<?php

declare(strict_types=1);

namespace Bunny\Storage;

class AuthenticationException extends Exception
{
    public function __construct(string $storageZoneName, string $accessKey, int $code = 0, \Exception $previous = null)
    {
        parent::__construct("Authentication failed for storage zone '{$storageZoneName}' with access key '{$accessKey}'.", $code, $previous);
    }
}
