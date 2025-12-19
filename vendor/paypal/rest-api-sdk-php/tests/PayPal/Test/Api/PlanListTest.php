<?php

namespace PayPal\Test\Api;

use PayPal\Api\PlanList;

/**
 * Class PlanList
 *
 * @package PayPal\Test\Api
 */
class PlanListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PlanList
     * @return string
     */
    public static function getJson()
    {
        return '{"plans":' .PlanTest::getJson() . ',"total_items":"TestSample","total_pages":"TestSample","links":' .LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return PlanList
     */
    public static function getObject()
    {
        return new PlanList(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return PlanList
     */
    public function testSerializationDeserialization()
    {
        $obj = new PlanList(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getPlans());
        $this->assertNotNull($obj->getTotalItems());
        $this->assertNotNull($obj->getTotalPages());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PlanList $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getPlans(), PlanTest::getObject());
        $this->assertEquals($obj->getTotalItems(), "TestSample");
        $this->assertEquals($obj->getTotalPages(), "TestSample");
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

}
