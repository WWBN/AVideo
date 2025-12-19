<?php

namespace PayPal\Test\Api;

use PayPal\Api\Error;

/**
 * Class Error
 *
 * @package PayPal\Test\Api
 */
class ErrorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Error
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"name":"TestSample","purchase_unit_reference_id":"TestSample","message":"TestSample","code":"TestSample","details":' . ErrorDetailsTest::getJson() . ',"processor_response":' . ProcessorResponseTest::getJson() . ',"fmf_details":' . FmfDetailsTest::getJson() . ',"information_link":"TestSample","debug_id":"TestSample","links":' . LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Error
     */
    public static function getObject()
    {
        return new Error(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Error
     */
    public function testSerializationDeserialization()
    {
        $obj = new Error(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getName());
        $this->assertNotNull($obj->getPurchaseUnitReferenceId());
        $this->assertNotNull($obj->getMessage());
        $this->assertNotNull($obj->getCode());
        $this->assertNotNull($obj->getDetails());
        $this->assertNotNull($obj->getProcessorResponse());
        $this->assertNotNull($obj->getFmfDetails());
        $this->assertNotNull($obj->getInformationLink());
        $this->assertNotNull($obj->getDebugId());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Error $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getName(), "TestSample");
        $this->assertEquals($obj->getPurchaseUnitReferenceId(), "TestSample");
        $this->assertEquals($obj->getMessage(), "TestSample");
        $this->assertEquals($obj->getCode(), "TestSample");
        $this->assertEquals($obj->getDetails(), ErrorDetailsTest::getObject());
        $this->assertEquals($obj->getProcessorResponse(), ProcessorResponseTest::getObject());
        $this->assertEquals($obj->getFmfDetails(), FmfDetailsTest::getObject());
        $this->assertEquals($obj->getInformationLink(), "TestSample");
        $this->assertEquals($obj->getDebugId(), "TestSample");
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }


}
