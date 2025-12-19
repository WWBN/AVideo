<?php

namespace PayPal\Test\Api;

use PayPal\Api\PayerInfo;

/**
 * Class PayerInfo
 *
 * @package PayPal\Test\Api
 */
class PayerInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PayerInfo
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"email":"TestSample","external_remember_me_id":"TestSample","account_number":"TestSample","salutation":"TestSample","first_name":"TestSample","middle_name":"TestSample","last_name":"TestSample","suffix":"TestSample","payer_id":"TestSample","phone":"TestSample","phone_type":"TestSample","birth_date":"TestSample","tax_id":"TestSample","tax_id_type":"TestSample","country_code":"TestSample","billing_address":' . AddressTest::getJson() . ',"shipping_address":' . ShippingAddressTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return PayerInfo
     */
    public static function getObject()
    {
        return new PayerInfo(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return PayerInfo
     */
    public function testSerializationDeserialization()
    {
        $obj = new PayerInfo(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getEmail());
        $this->assertNotNull($obj->getExternalRememberMeId());
        $this->assertNotNull($obj->getAccountNumber());
        $this->assertNotNull($obj->getSalutation());
        $this->assertNotNull($obj->getFirstName());
        $this->assertNotNull($obj->getMiddleName());
        $this->assertNotNull($obj->getLastName());
        $this->assertNotNull($obj->getSuffix());
        $this->assertNotNull($obj->getPayerId());
        $this->assertNotNull($obj->getPhone());
        $this->assertNotNull($obj->getPhoneType());
        $this->assertNotNull($obj->getBirthDate());
        $this->assertNotNull($obj->getTaxId());
        $this->assertNotNull($obj->getTaxIdType());
        $this->assertNotNull($obj->getCountryCode());
        $this->assertNotNull($obj->getBillingAddress());
        $this->assertNotNull($obj->getShippingAddress());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PayerInfo $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getEmail(), "TestSample");
        $this->assertEquals($obj->getExternalRememberMeId(), "TestSample");
        $this->assertEquals($obj->getAccountNumber(), "TestSample");
        $this->assertEquals($obj->getSalutation(), "TestSample");
        $this->assertEquals($obj->getFirstName(), "TestSample");
        $this->assertEquals($obj->getMiddleName(), "TestSample");
        $this->assertEquals($obj->getLastName(), "TestSample");
        $this->assertEquals($obj->getSuffix(), "TestSample");
        $this->assertEquals($obj->getPayerId(), "TestSample");
        $this->assertEquals($obj->getPhone(), "TestSample");
        $this->assertEquals($obj->getPhoneType(), "TestSample");
        $this->assertEquals($obj->getBirthDate(), "TestSample");
        $this->assertEquals($obj->getTaxId(), "TestSample");
        $this->assertEquals($obj->getTaxIdType(), "TestSample");
        $this->assertEquals($obj->getCountryCode(), "TestSample");
        $this->assertEquals($obj->getBillingAddress(), AddressTest::getObject());
        $this->assertEquals($obj->getShippingAddress(), ShippingAddressTest::getObject());
    }


}
