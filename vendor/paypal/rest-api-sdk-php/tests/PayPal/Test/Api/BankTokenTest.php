<?php

namespace PayPal\Test\Api;

use PayPal\Api\BankToken;

/**
 * Class BankToken
 *
 * @package PayPal\Test\Api
 */
class BankTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object BankToken
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"bank_id":"TestSample","external_customer_id":"TestSample","mandate_reference_number":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return BankToken
     */
    public static function getObject()
    {
        return new BankToken(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return BankToken
     */
    public function testSerializationDeserialization()
    {
        $obj = new BankToken(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getBankId());
        $this->assertNotNull($obj->getExternalCustomerId());
        $this->assertNotNull($obj->getMandateReferenceNumber());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param BankToken $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getBankId(), "TestSample");
        $this->assertEquals($obj->getExternalCustomerId(), "TestSample");
        $this->assertEquals($obj->getMandateReferenceNumber(), "TestSample");
    }


}
