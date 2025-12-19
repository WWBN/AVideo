<?php

use PayPal\Common\PayPalUserAgent;

class UserAgentTest extends PHPUnit_Framework_TestCase
{

    public function testGetValue()
    {
        $ua = PayPalUserAgent::getValue("name", "version");
        list($id, $version, $features) = sscanf($ua, "PayPalSDK/%s %s (%[^[]])");

        // Check that we pass the useragent in the expected format
        $this->assertNotNull($id);
        $this->assertNotNull($version);
        $this->assertNotNull($features);

        $this->assertEquals("name", $id);
        $this->assertEquals("version", $version);

        // Check that we pass in these mininal features
        $this->assertThat($features, $this->stringContains("os="));
        $this->assertThat($features, $this->stringContains("bit="));
        $this->assertThat($features, $this->stringContains("platform-ver="));
        $this->assertGreaterThan(5, count(explode(';', $features)));
    }
}
