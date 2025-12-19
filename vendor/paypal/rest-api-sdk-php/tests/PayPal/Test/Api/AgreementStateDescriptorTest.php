<?php

namespace PayPal\Test\Api;

use PayPal\Api\AgreementStateDescriptor;

/**
 * Class AgreementStateDescriptor
 *
 * @package PayPal\Test\Api
 */
class AgreementStateDescriptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object AgreementStateDescriptor
     * @return string
     */
    public static function getJson()
    {
        return '{"note":"TestSample","amount":' .CurrencyTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return AgreementStateDescriptor
     */
    public static function getObject()
    {
        return new AgreementStateDescriptor(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return AgreementStateDescriptor
     */
    public function testSerializationDeserialization()
    {
        $obj = new AgreementStateDescriptor(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getNote());
        $this->assertNotNull($obj->getAmount());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param AgreementStateDescriptor $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getNote(), "TestSample");
        $this->assertEquals($obj->getAmount(), CurrencyTest::getObject());
    }

}
