<?php

namespace Tests\Unit;

use Tests\TestCase;

class HealthCheckXSendFileTest extends TestCase
{
    private $expected;

    protected function setUp(): void
    {
        parent::setUp();
        if (!function_exists('isXSendFileTestResponse')) {
            require_once \APP_ROOT . '/admin/health_check_functions.php';
        }
        $this->expected = file_get_contents(\APP_ROOT . '/view/xsendfile.html');
    }

    public function testExactTestPageIsAccepted()
    {
        $this->assertTrue(\isXSendFileTestResponse($this->expected, $this->expected));
    }

    public function testTestPageModifiedByCdnIsAccepted()
    {
        // Issue #10656: Cloudflare Web Analytics injects its beacon before </body>
        $beacon = '<script defer src="https://static.cloudflareinsights.com/beacon.min.js"></script>';
        $response = str_replace('</body>', $beacon . '</body>', $this->expected);

        $this->assertNotSame($this->expected, $response);
        $this->assertTrue(\isXSendFileTestResponse($response, $this->expected));
    }

    public function testResponsesWithoutTheTestPageAreRejected()
    {
        // XSendFile off: xsendfile.php sends headers only, so the body is empty
        $this->assertFalse(\isXSendFileTestResponse('', $this->expected));
        // Request failed
        $this->assertFalse(\isXSendFileTestResponse(false, $this->expected));
        $this->assertFalse(\isXSendFileTestResponse(null, $this->expected));
        // mod_xsendfile missing: the rewrite does not apply and another page is returned
        $this->assertFalse(\isXSendFileTestResponse('<html><body>404 Not Found</body></html>', $this->expected));
    }

    public function testMarkerIsOnlyUsedWhenTheTestPageContainsIt()
    {
        $this->assertFalse(\isXSendFileTestResponse('Your Apache XSendFIle file is working', 'another fixture'));
    }
}
