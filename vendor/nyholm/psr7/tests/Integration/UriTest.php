<?php

namespace Tests\Nyholm\Psr7\Integration;

use Http\Psr7Test\UriIntegrationTest;
use Nyholm\Psr7\Uri;

class UriTest extends UriIntegrationTest
{
    public function createUri($uri)
    {
        return new Uri($uri);
    }
}
