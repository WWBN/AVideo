<?php

namespace PayPal\Test\Api;

use PayPal\Api\PotentialPayerInfo;

/**
 * Class PotentialPayerInfo
 *
 * @package PayPal\Test\Api
 */
class PotentialPayerInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PotentialPayerInfo
     * @return string
     */
    public static function getJson()
    {
        return '{"email":"TestSample","external_remember_me_id":"TestSample","account_number":"TestSample","billing_address":' .AddressTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return PotentialPayerInfo
     */
    public static function getObject()
    {
        return new PotentialPayerInfo(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return PotentialPayerInfo
     */
    public function testSerializationDeserialization()
    {
        $obj = new PotentialPayerInfo(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getEmail());
        $this->assertNotNull($obj->getExternalRememberMeId());
        $this->assertNotNull($obj->getAccountNumber());
        $this->assertNotNull($obj->getBillingAddress());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PotentialPayerInfo $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getEmail(), "TestSample");
        $this->assertEquals($obj->getExternalRememberMeId(), "TestSample");
        $this->assertEquals($obj->getAccountNumber(), "TestSample");
        $this->assertEquals($obj->getBillingAddress(), AddressTest::getObject());
    }


}
