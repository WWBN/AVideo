<?php

namespace PayPal\Test\Api;

use PayPal\Api\RedirectUrls;

/**
 * Class RedirectUrls
 *
 * @package PayPal\Test\Api
 */
class RedirectUrlsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object RedirectUrls
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"return_url":"http://www.google.com","cancel_url":"http://www.google.com"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return RedirectUrls
     */
    public static function getObject()
    {
        return new RedirectUrls(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return RedirectUrls
     */
    public function testSerializationDeserialization()
    {
        $obj = new RedirectUrls(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getReturnUrl());
        $this->assertNotNull($obj->getCancelUrl());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param RedirectUrls $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getReturnUrl(), "http://www.google.com");
        $this->assertEquals($obj->getCancelUrl(), "http://www.google.com");
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ReturnUrl is not a fully qualified URL
     */
    public function testUrlValidationForReturnUrl()
    {
        $obj = new RedirectUrls();
        $obj->setReturnUrl(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage CancelUrl is not a fully qualified URL
     */
    public function testUrlValidationForCancelUrl()
    {
        $obj = new RedirectUrls();
        $obj->setCancelUrl(null);
    }

}
