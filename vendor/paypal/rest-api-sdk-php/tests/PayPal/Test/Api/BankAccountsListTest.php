<?php

namespace PayPal\Test\Api;

use PayPal\Api\BankAccountsList;

/**
 * Class BankAccountsList
 *
 * @package PayPal\Test\Api
 */
class BankAccountsListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object BankAccountsList
     * @return string
     */
    public static function getJson()
    {
        return '{"bank-accounts":' .BankAccountTest::getJson() . ',"count":123,"next_id":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return BankAccountsList
     */
    public static function getObject()
    {
        return new BankAccountsList(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return BankAccountsList
     */
    public function testSerializationDeserialization()
    {
        $obj = new BankAccountsList(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getBankAccounts());
        $this->assertNotNull($obj->getCount());
        $this->assertNotNull($obj->getNextId());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param BankAccountsList $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getBankAccounts(), BankAccountTest::getObject());
        $this->assertEquals($obj->getCount(), 123);
        $this->assertEquals($obj->getNextId(), "TestSample");
    }

}
