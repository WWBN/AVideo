<?php

namespace Tests\Security;

use PHPUnit\Framework\TestCase;

/**
 * plugin/CDN/status.json.php is called server-to-server by the CDN marketplace (no Origin/Referer, UA
 * "AVideoMarketplace") when a site is added or rebuilt. autoCSRFGuard() redirected it to the login page,
 * so no site could be connected to the CDN. It is exempted because it does not use the session and is
 * authenticated by the CDN plugin key.
 */
class CDNStatusMarketplaceCallTest extends TestCase
{
    public function testStatusEndpointIsInTheBuiltinCsrfBypassList()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/objects/functionsSecurity.php');
        $start = strpos($source, 'static $builtinBypass = [');
        $this->assertNotFalse($start);
        $list = substr($source, $start, strpos($source, '];', $start) - $start);

        $this->assertStringContainsString("'plugin/CDN/status.json.php'", $list);
    }

    public function testStatusEndpointChecksTheKeyBeforeSavingAndDoesNotUseTheSession()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/plugin/CDN/status.json.php');

        $keyCheck = strpos($source, "hash_equals(\$obj->key, \$_REQUEST['key'])");
        $this->assertNotFalse($keyCheck, 'status.json.php must compare the CDN key');
        $this->assertLessThan(strpos($source, '->setDataObject('), $keyCheck);
        $this->assertStringContainsString('_session_write_close();', $source);
        $this->assertStringNotContainsString('User::', $source);
    }
}
