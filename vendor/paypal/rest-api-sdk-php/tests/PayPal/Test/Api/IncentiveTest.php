<?php

namespace PayPal\Test\Api;

use PayPal\Api\Incentive;

/**
 * Class Incentive
 *
 * @package PayPal\Test\Api
 */
class IncentiveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Incentive
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","code":"TestSample","name":"TestSample","description":"TestSample","minimum_purchase_amount":' . CurrencyTest::getJson() . ',"logo_image_url":"http://www.google.com","expiry_date":"TestSample","type":"TestSample","terms":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Incentive
     */
    public static function getObject()
    {
        return new Incentive(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Incentive
     */
    public function testSerializationDeserialization()
    {
        $obj = new Incentive(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getCode());
        $this->assertNotNull($obj->getName());
        $this->assertNotNull($obj->getDescription());
        $this->assertNotNull($obj->getMinimumPurchaseAmount());
        $this->assertNotNull($obj->getLogoImageUrl());
        $this->assertNotNull($obj->getExpiryDate());
        $this->assertNotNull($obj->getType());
        $this->assertNotNull($obj->getTerms());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Incentive $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getCode(), "TestSample");
        $this->assertEquals($obj->getName(), "TestSample");
        $this->assertEquals($obj->getDescription(), "TestSample");
        $this->assertEquals($obj->getMinimumPurchaseAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getLogoImageUrl(), "http://www.google.com");
        $this->assertEquals($obj->getExpiryDate(), "TestSample");
        $this->assertEquals($obj->getType(), "TestSample");
        $this->assertEquals($obj->getTerms(), "TestSample");
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage LogoImageUrl is not a fully qualified URL
     */
    public function testUrlValidationForLogoImageUrl()
    {
        $obj = new Incentive();
        $obj->setLogoImageUrl(null);
    }

}
