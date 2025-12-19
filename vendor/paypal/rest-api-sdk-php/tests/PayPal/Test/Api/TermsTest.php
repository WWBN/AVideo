<?php

namespace PayPal\Test\Api;

use PayPal\Api\Terms;

/**
 * Class Terms
 *
 * @package PayPal\Test\Api
 */
class TermsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Terms
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","type":"TestSample","max_billing_amount":' .CurrencyTest::getJson() . ',"occurrences":"TestSample","amount_range":' .CurrencyTest::getJson() . ',"buyer_editable":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return Terms
     */
    public static function getObject()
    {
        return new Terms(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return Terms
     */
    public function testSerializationDeserialization()
    {
        $obj = new Terms(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getType());
        $this->assertNotNull($obj->getMaxBillingAmount());
        $this->assertNotNull($obj->getOccurrences());
        $this->assertNotNull($obj->getAmountRange());
        $this->assertNotNull($obj->getBuyerEditable());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Terms $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getType(), "TestSample");
        $this->assertEquals($obj->getMaxBillingAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getOccurrences(), "TestSample");
        $this->assertEquals($obj->getAmountRange(), CurrencyTest::getObject());
        $this->assertEquals($obj->getBuyerEditable(), "TestSample");
    }

}
