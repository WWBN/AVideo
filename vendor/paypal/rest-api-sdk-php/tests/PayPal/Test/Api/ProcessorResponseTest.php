<?php

namespace PayPal\Test\Api;

use PayPal\Api\ProcessorResponse;

/**
 * Class ProcessorResponse
 *
 * @package PayPal\Test\Api
 */
class ProcessorResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object ProcessorResponse
     * @return string
     */
    public static function getJson()
    {
        return '{"response_code":"TestSample","avs_code":"TestSample","cvv_code":"TestSample","advice_code":"TestSample","eci_submitted":"TestSample","vpas":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return ProcessorResponse
     */
    public static function getObject()
    {
        return new ProcessorResponse(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return ProcessorResponse
     */
    public function testSerializationDeserialization()
    {
        $obj = new ProcessorResponse(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getResponseCode());
        $this->assertNotNull($obj->getAvsCode());
        $this->assertNotNull($obj->getCvvCode());
        $this->assertNotNull($obj->getAdviceCode());
        $this->assertNotNull($obj->getEciSubmitted());
        $this->assertNotNull($obj->getVpas());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param ProcessorResponse $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getResponseCode(), "TestSample");
        $this->assertEquals($obj->getAvsCode(), "TestSample");
        $this->assertEquals($obj->getCvvCode(), "TestSample");
        $this->assertEquals($obj->getAdviceCode(), "TestSample");
        $this->assertEquals($obj->getEciSubmitted(), "TestSample");
        $this->assertEquals($obj->getVpas(), "TestSample");
    }


}
