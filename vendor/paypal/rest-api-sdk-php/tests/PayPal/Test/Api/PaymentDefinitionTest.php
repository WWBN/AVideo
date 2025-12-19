<?php

namespace PayPal\Test\Api;

use PayPal\Api\PaymentDefinition;

/**
 * Class PaymentDefinition
 *
 * @package PayPal\Test\Api
 */
class PaymentDefinitionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PaymentDefinition
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","name":"TestSample","type":"TestSample","frequency_interval":"TestSample","frequency":"TestSample","cycles":"TestSample","amount":' .CurrencyTest::getJson() . ',"charge_models":' .ChargeModelTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return PaymentDefinition
     */
    public static function getObject()
    {
        return new PaymentDefinition(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return PaymentDefinition
     */
    public function testSerializationDeserialization()
    {
        $obj = new PaymentDefinition(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getName());
        $this->assertNotNull($obj->getType());
        $this->assertNotNull($obj->getFrequencyInterval());
        $this->assertNotNull($obj->getFrequency());
        $this->assertNotNull($obj->getCycles());
        $this->assertNotNull($obj->getAmount());
        $this->assertNotNull($obj->getChargeModels());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PaymentDefinition $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getName(), "TestSample");
        $this->assertEquals($obj->getType(), "TestSample");
        $this->assertEquals($obj->getFrequencyInterval(), "TestSample");
        $this->assertEquals($obj->getFrequency(), "TestSample");
        $this->assertEquals($obj->getCycles(), "TestSample");
        $this->assertEquals($obj->getAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getChargeModels(), ChargeModelTest::getObject());
    }

}
