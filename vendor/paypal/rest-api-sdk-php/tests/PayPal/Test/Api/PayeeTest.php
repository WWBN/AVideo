<?php

namespace PayPal\Test\Api;

use PayPal\Api\Payee;

/**
 * Class Payee
 *
 * @package PayPal\Test\Api
 */
class PayeeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Payee
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"email":"TestSample","merchant_id":"TestSample","first_name":"TestSample","last_name":"TestSample","account_number":"TestSample","phone":' . PhoneTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Payee
     */
    public static function getObject()
    {
        return new Payee(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Payee
     */
    public function testSerializationDeserialization()
    {
        $obj = new Payee(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getEmail());
        $this->assertNotNull($obj->getMerchantId());
        $this->assertNotNull($obj->getFirstName());
        $this->assertNotNull($obj->getLastName());
        $this->assertNotNull($obj->getAccountNumber());
        $this->assertNotNull($obj->getPhone());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Payee $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getEmail(), "TestSample");
        $this->assertEquals($obj->getMerchantId(), "TestSample");
        $this->assertEquals($obj->getFirstName(), "TestSample");
        $this->assertEquals($obj->getLastName(), "TestSample");
        $this->assertEquals($obj->getAccountNumber(), "TestSample");
        $this->assertEquals($obj->getPhone(), PhoneTest::getObject());
    }


}
