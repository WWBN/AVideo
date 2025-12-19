<?php

namespace PayPal\Test\Api;

use PayPal\Api\InputFields;

/**
 * Class InputFields
 *
 * @package PayPal\Test\Api
 */
class InputFieldsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object InputFields
     * @return string
     */
    public static function getJson()
    {
        return json_encode(json_decode('{"allow_note":true,"no_shipping":123,"address_override":123}'));
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return InputFields
     */
    public static function getObject()
    {
        return new InputFields(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return InputFields
     */
    public function testSerializationDeserialization()
    {
        $obj = new InputFields(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getAllowNote());
        $this->assertNotNull($obj->getNoShipping());
        $this->assertNotNull($obj->getAddressOverride());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param InputFields $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getAllowNote(), true);
        $this->assertEquals($obj->getNoShipping(), 123);
        $this->assertEquals($obj->getAddressOverride(), 123);
    }

}
