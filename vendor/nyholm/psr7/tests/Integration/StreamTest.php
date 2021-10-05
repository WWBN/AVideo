<?php

namespace Tests\Nyholm\Psr7\Integration;

use Http\Psr7Test\StreamIntegrationTest;
use Nyholm\Psr7\Stream;

class StreamTest extends StreamIntegrationTest
{
    public function createStream($data)
    {
        return Stream::create($data);
    }
}
