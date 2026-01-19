<?php

namespace PayPal\Test\Api;

use PayPal\Api\CreateProfileResponse;

/**
 * Class CreateProfileResponse
 *
 * @package PayPal\Test\Api
 */
class CreateProfileResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object CreateProfileResponse
     * @return string
     */
    public static function getJson()
    {
        return json_encode(json_decode('{"id":"TestSample"}'));
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return CreateProfileResponse
     */
    public static function getObject()
    {
        return new CreateProfileResponse(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return CreateProfileResponse
     */
    public function testSerializationDeserialization()
    {
        $obj = new CreateProfileResponse(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param CreateProfileResponse $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
    }

}
