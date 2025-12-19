<?php

namespace PayPal\Test\Api;

use PayPal\Api\RecipientBankingInstruction;

/**
 * Class RecipientBankingInstruction
 *
 * @package PayPal\Test\Api
 */
class RecipientBankingInstructionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object RecipientBankingInstruction
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"bank_name":"TestSample","account_holder_name":"TestSample","account_number":"TestSample","routing_number":"TestSample","international_bank_account_number":"TestSample","bank_identifier_code":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return RecipientBankingInstruction
     */
    public static function getObject()
    {
        return new RecipientBankingInstruction(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return RecipientBankingInstruction
     */
    public function testSerializationDeserialization()
    {
        $obj = new RecipientBankingInstruction(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getBankName());
        $this->assertNotNull($obj->getAccountHolderName());
        $this->assertNotNull($obj->getAccountNumber());
        $this->assertNotNull($obj->getRoutingNumber());
        $this->assertNotNull($obj->getInternationalBankAccountNumber());
        $this->assertNotNull($obj->getBankIdentifierCode());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param RecipientBankingInstruction $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getBankName(), "TestSample");
        $this->assertEquals($obj->getAccountHolderName(), "TestSample");
        $this->assertEquals($obj->getAccountNumber(), "TestSample");
        $this->assertEquals($obj->getRoutingNumber(), "TestSample");
        $this->assertEquals($obj->getInternationalBankAccountNumber(), "TestSample");
        $this->assertEquals($obj->getBankIdentifierCode(), "TestSample");
    }


}
