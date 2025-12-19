<?php

namespace PayPal\Test\Api;

use PayPal\Api\InstallmentOption;

/**
 * Class InstallmentOption
 *
 * @package PayPal\Test\Api
 */
class InstallmentOptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object InstallmentOption
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"term":123,"monthly_payment":' . CurrencyTest::getJson() . ',"discount_amount":' . CurrencyTest::getJson() . ',"discount_percentage":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return InstallmentOption
     */
    public static function getObject()
    {
        return new InstallmentOption(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return InstallmentOption
     */
    public function testSerializationDeserialization()
    {
        $obj = new InstallmentOption(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getTerm());
        $this->assertNotNull($obj->getMonthlyPayment());
        $this->assertNotNull($obj->getDiscountAmount());
        $this->assertNotNull($obj->getDiscountPercentage());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param InstallmentOption $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getTerm(), 123);
        $this->assertEquals($obj->getMonthlyPayment(), CurrencyTest::getObject());
        $this->assertEquals($obj->getDiscountAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getDiscountPercentage(), "TestSample");
    }


}
