<?php

namespace Tests\Nyholm\Psr7\Integration;

use Http\Psr7Test\ResponseIntegrationTest;
use Nyholm\Psr7\Response;

class ResponseTest extends ResponseIntegrationTest
{
    public function createSubject()
    {
        return new Response();
    }
}
