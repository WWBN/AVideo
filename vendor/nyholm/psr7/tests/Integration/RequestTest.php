<?php

namespace Tests\Nyholm\Psr7\Integration;

use Http\Psr7Test\RequestIntegrationTest;
use Nyholm\Psr7\Request;

class RequestTest extends RequestIntegrationTest
{
    public function createSubject()
    {
        return new Request('GET', '/');
    }
}
