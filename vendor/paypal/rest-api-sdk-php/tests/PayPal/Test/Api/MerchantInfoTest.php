<?php

namespace PayPal\Test\Api;

use PayPal\Api\MerchantInfo;

/**
 * Class MerchantInfo
 *
 * @package PayPal\Test\Api
 */
class MerchantInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object MerchantInfo
     * @return string
     */
    public static function getJson()
    {
        return '{"email":"TestSample","first_name":"TestSample","last_name":"TestSample","address":' .AddressTest::getJson() . ',"business_name":"TestSample","phone":' .PhoneTest::getJson() . ',"fax":' .PhoneTest::getJson() . ',"website":"TestSample","tax_id":"TestSample","additional_info":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return MerchantInfo
     */
    public static function getObject()
    {
        return new MerchantInfo(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return MerchantInfo
     */
    public function testSerializationDeserialization()
    {
        $obj = new MerchantInfo(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getEmail());
        $this->assertNotNull($obj->getFirstName());
        $this->assertNotNull($obj->getLastName());
        $this->assertNotNull($obj->getAddress());
        $this->assertNotNull($obj->getBusinessName());
        $this->assertNotNull($obj->getPhone());
        $this->assertNotNull($obj->getFax());
        $this->assertNotNull($obj->getWebsite());
        $this->assertNotNull($obj->getTaxId());
        $this->assertNotNull($obj->getAdditionalInfo());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param MerchantInfo $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getEmail(), "TestSample");
        $this->assertEquals($obj->getFirstName(), "TestSample");
        $this->assertEquals($obj->getLastName(), "TestSample");
        $this->assertEquals($obj->getAddress(), AddressTest::getObject());
        $this->assertEquals($obj->getBusinessName(), "TestSample");
        $this->assertEquals($obj->getPhone(), PhoneTest::getObject());
        $this->assertEquals($obj->getFax(), PhoneTest::getObject());
        $this->assertEquals($obj->getWebsite(), "TestSample");
        $this->assertEquals($obj->getTaxId(), "TestSample");
        $this->assertEquals($obj->getAdditionalInfo(), "TestSample");
    }

}
