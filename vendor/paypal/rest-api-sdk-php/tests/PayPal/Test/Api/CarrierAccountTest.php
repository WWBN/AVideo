<?php

namespace PayPal\Test\Api;

use PayPal\Api\CarrierAccount;

/**
 * Class CarrierAccount
 *
 * @package PayPal\Test\Api
 */
class CarrierAccountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object CarrierAccount
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","phone_number":"TestSample","external_customer_id":"TestSample","phone_source":"TestSample","country_code":' .CountryCodeTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return CarrierAccount
     */
    public static function getObject()
    {
        return new CarrierAccount(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return CarrierAccount
     */
    public function testSerializationDeserialization()
    {
        $obj = new CarrierAccount(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getPhoneNumber());
        $this->assertNotNull($obj->getExternalCustomerId());
        $this->assertNotNull($obj->getPhoneSource());
        $this->assertNotNull($obj->getCountryCode());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param CarrierAccount $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getPhoneNumber(), "TestSample");
        $this->assertEquals($obj->getExternalCustomerId(), "TestSample");
        $this->assertEquals($obj->getPhoneSource(), "TestSample");
        $this->assertEquals($obj->getCountryCode(), CountryCodeTest::getObject());
    }


}
