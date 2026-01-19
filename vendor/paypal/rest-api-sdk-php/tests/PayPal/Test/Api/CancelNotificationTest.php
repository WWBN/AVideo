<?php

namespace PayPal\Test\Api;

use PayPal\Api\CancelNotification;

/**
 * Class CancelNotification
 *
 * @package PayPal\Test\Api
 */
class CancelNotificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object CancelNotification
     * @return string
     */
    public static function getJson()
    {
        return '{"subject":"TestSample","note":"TestSample","send_to_merchant":true,"send_to_payer":true}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return CancelNotification
     */
    public static function getObject()
    {
        return new CancelNotification(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return CancelNotification
     */
    public function testSerializationDeserialization()
    {
        $obj = new CancelNotification(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getSubject());
        $this->assertNotNull($obj->getNote());
        $this->assertNotNull($obj->getSendToMerchant());
        $this->assertNotNull($obj->getSendToPayer());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param CancelNotification $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getSubject(), "TestSample");
        $this->assertEquals($obj->getNote(), "TestSample");
        $this->assertEquals($obj->getSendToMerchant(), true);
        $this->assertEquals($obj->getSendToPayer(), true);
    }

}
