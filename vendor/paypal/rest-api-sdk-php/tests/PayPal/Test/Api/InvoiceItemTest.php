<?php

namespace PayPal\Test\Api;

use PayPal\Api\InvoiceItem;

/**
 * Class InvoiceItem
 *
 * @package PayPal\Test\Api
 */
class InvoiceItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object InvoiceItem
     * @return string
     */
    public static function getJson()
    {
        return '{"name":"TestSample","description":"TestSample","quantity":"12.34","unit_price":' .CurrencyTest::getJson() . ',"tax":' .TaxTest::getJson() . ',"date":"TestSample","discount":' .CostTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return InvoiceItem
     */
    public static function getObject()
    {
        return new InvoiceItem(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return InvoiceItem
     */
    public function testSerializationDeserialization()
    {
        $obj = new InvoiceItem(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getName());
        $this->assertNotNull($obj->getDescription());
        $this->assertNotNull($obj->getQuantity());
        $this->assertNotNull($obj->getUnitPrice());
        $this->assertNotNull($obj->getTax());
        $this->assertNotNull($obj->getDate());
        $this->assertNotNull($obj->getDiscount());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param InvoiceItem $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getName(), "TestSample");
        $this->assertEquals($obj->getDescription(), "TestSample");
        $this->assertEquals($obj->getQuantity(), "12.34");
        $this->assertEquals($obj->getUnitPrice(), CurrencyTest::getObject());
        $this->assertEquals($obj->getTax(), TaxTest::getObject());
        $this->assertEquals($obj->getDate(), "TestSample");
        $this->assertEquals($obj->getDiscount(), CostTest::getObject());
    }

}
