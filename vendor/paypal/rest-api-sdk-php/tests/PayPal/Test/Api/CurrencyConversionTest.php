<?php

namespace PayPal\Test\Api;

use PayPal\Api\CurrencyConversion;

/**
 * Class CurrencyConversion
 *
 * @package PayPal\Test\Api
 */
class CurrencyConversionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object CurrencyConversion
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"conversion_date":"TestSample","from_currency":"TestSample","from_amount":"TestSample","to_currency":"TestSample","to_amount":"TestSample","conversion_type":"TestSample","conversion_type_changeable":true,"web_url":"http://www.google.com","links":' . LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return CurrencyConversion
     */
    public static function getObject()
    {
        return new CurrencyConversion(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return CurrencyConversion
     */
    public function testSerializationDeserialization()
    {
        $obj = new CurrencyConversion(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getConversionDate());
        $this->assertNotNull($obj->getFromCurrency());
        $this->assertNotNull($obj->getFromAmount());
        $this->assertNotNull($obj->getToCurrency());
        $this->assertNotNull($obj->getToAmount());
        $this->assertNotNull($obj->getConversionType());
        $this->assertNotNull($obj->getConversionTypeChangeable());
        $this->assertNotNull($obj->getWebUrl());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param CurrencyConversion $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getConversionDate(), "TestSample");
        $this->assertEquals($obj->getFromCurrency(), "TestSample");
        $this->assertEquals($obj->getFromAmount(), "TestSample");
        $this->assertEquals($obj->getToCurrency(), "TestSample");
        $this->assertEquals($obj->getToAmount(), "TestSample");
        $this->assertEquals($obj->getConversionType(), "TestSample");
        $this->assertEquals($obj->getConversionTypeChangeable(), true);
        $this->assertEquals($obj->getWebUrl(), "http://www.google.com");
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage WebUrl is not a fully qualified URL
     */
    public function testUrlValidationForWebUrl()
    {
        $obj = new CurrencyConversion();
        $obj->setWebUrl(null);
    }

    public function testUrlValidationForWebUrlDeprecated()
    {
        $obj = new CurrencyConversion();
        $obj->setWebUrl(null);
        $this->assertNull($obj->getWebUrl());
    }

}
