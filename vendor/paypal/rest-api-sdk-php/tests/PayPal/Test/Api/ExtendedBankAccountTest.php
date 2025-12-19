<?php

namespace PayPal\Test\Api;

use PayPal\Api\ExtendedBankAccount;

/**
 * Class ExtendedBankAccount
 *
 * @package PayPal\Test\Api
 */
class ExtendedBankAccountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object ExtendedBankAccount
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"mandate_reference_number":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return ExtendedBankAccount
     */
    public static function getObject()
    {
        return new ExtendedBankAccount(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return ExtendedBankAccount
     */
    public function testSerializationDeserialization()
    {
        $obj = new ExtendedBankAccount(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getMandateReferenceNumber());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param ExtendedBankAccount $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getMandateReferenceNumber(), "TestSample");
    }


}
