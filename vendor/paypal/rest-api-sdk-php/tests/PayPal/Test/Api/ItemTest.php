<?php

namespace PayPal\Test\Api;

use PayPal\Api\Item;

/**
 * Class Item
 *
 * @package PayPal\Test\Api
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Item
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"sku":"TestSample","name":"TestSample","description":"TestSample","quantity":"12.34","price":"12.34","currency":"TestSample","tax":"12.34","url":"http://www.google.com","category":"TestSample","weight":' . MeasurementTest::getJson() . ',"length":' . MeasurementTest::getJson() . ',"height":' . MeasurementTest::getJson() . ',"width":' . MeasurementTest::getJson() . ',"supplementary_data":' . NameValuePairTest::getJson() . ',"postback_data":' . NameValuePairTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Item
     */
    public static function getObject()
    {
        return new Item(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Item
     */
    public function testSerializationDeserialization()
    {
        $obj = new Item(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getSku());
        $this->assertNotNull($obj->getName());
        $this->assertNotNull($obj->getDescription());
        $this->assertNotNull($obj->getQuantity());
        $this->assertNotNull($obj->getPrice());
        $this->assertNotNull($obj->getCurrency());
        $this->assertNotNull($obj->getTax());
        $this->assertNotNull($obj->getUrl());
        $this->assertNotNull($obj->getCategory());
        $this->assertNotNull($obj->getWeight());
        $this->assertNotNull($obj->getLength());
        $this->assertNotNull($obj->getHeight());
        $this->assertNotNull($obj->getWidth());
        $this->assertNotNull($obj->getSupplementaryData());
        $this->assertNotNull($obj->getPostbackData());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Item $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getSku(), "TestSample");
        $this->assertEquals($obj->getName(), "TestSample");
        $this->assertEquals($obj->getDescription(), "TestSample");
        $this->assertEquals($obj->getQuantity(), "12.34");
        $this->assertEquals($obj->getPrice(), "12.34");
        $this->assertEquals($obj->getCurrency(), "TestSample");
        $this->assertEquals($obj->getTax(), "12.34");
        $this->assertEquals($obj->getUrl(), "http://www.google.com");
        $this->assertEquals($obj->getCategory(), "TestSample");
        $this->assertEquals($obj->getWeight(), MeasurementTest::getObject());
        $this->assertEquals($obj->getLength(), MeasurementTest::getObject());
        $this->assertEquals($obj->getHeight(), MeasurementTest::getObject());
        $this->assertEquals($obj->getWidth(), MeasurementTest::getObject());
        $this->assertEquals($obj->getSupplementaryData(), NameValuePairTest::getObject());
        $this->assertEquals($obj->getPostbackData(), NameValuePairTest::getObject());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Url is not a fully qualified URL
     */
    public function testUrlValidationForUrl()
    {
        $obj = new Item();
        $obj->setUrl(null);
    }

}
