<?php

namespace PayPal\Test\Api;

use PayPal\Api\RelatedResources;

/**
 * Class RelatedResources
 *
 * @package PayPal\Test\Api
 */
class RelatedResourcesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object RelatedResources
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"sale":' . SaleTest::getJson() . ',"authorization":' . AuthorizationTest::getJson() . ',"order":' . OrderTest::getJson() . ',"capture":' . CaptureTest::getJson() . ',"refund":' . RefundTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return RelatedResources
     */
    public static function getObject()
    {
        return new RelatedResources(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return RelatedResources
     */
    public function testSerializationDeserialization()
    {
        $obj = new RelatedResources(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getSale());
        $this->assertNotNull($obj->getAuthorization());
        $this->assertNotNull($obj->getOrder());
        $this->assertNotNull($obj->getCapture());
        $this->assertNotNull($obj->getRefund());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param RelatedResources $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getSale(), SaleTest::getObject());
        $this->assertEquals($obj->getAuthorization(), AuthorizationTest::getObject());
        $this->assertEquals($obj->getOrder(), OrderTest::getObject());
        $this->assertEquals($obj->getCapture(), CaptureTest::getObject());
        $this->assertEquals($obj->getRefund(), RefundTest::getObject());
    }


}
