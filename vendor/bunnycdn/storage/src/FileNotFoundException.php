<?php

declare(strict_types=1);

namespace Bunny\Storage;

class FileNotFoundException extends Exception
{
    public function __construct(string $path, int $code = 0, \Exception $previous = null)
    {
        parent::__construct("Could not find part of the object path: {$path}", $code, $previous);
    }
}
