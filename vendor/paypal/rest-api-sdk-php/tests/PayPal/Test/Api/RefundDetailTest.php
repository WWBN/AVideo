<?php

namespace PayPal\Test\Api;

use PayPal\Api\RefundDetail;

/**
 * Class RefundDetail
 *
 * @package PayPal\Test\Api
 */
class RefundDetailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object RefundDetail
     * @return string
     */
    public static function getJson()
    {
        return '{"type":"TestSample","date":"TestSample","note":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return RefundDetail
     */
    public static function getObject()
    {
        return new RefundDetail(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return RefundDetail
     */
    public function testSerializationDeserialization()
    {
        $obj = new RefundDetail(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getType());
        $this->assertNotNull($obj->getDate());
        $this->assertNotNull($obj->getNote());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param RefundDetail $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getType(), "TestSample");
        $this->assertEquals($obj->getDate(), "TestSample");
        $this->assertEquals($obj->getNote(), "TestSample");
    }

}
