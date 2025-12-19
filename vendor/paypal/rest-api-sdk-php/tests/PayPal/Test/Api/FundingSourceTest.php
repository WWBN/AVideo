<?php

namespace PayPal\Test\Api;

use PayPal\Api\FundingSource;

/**
 * Class FundingSource
 *
 * @package PayPal\Test\Api
 */
class FundingSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object FundingSource
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"funding_mode":"TestSample","funding_instrument_type":"TestSample","soft_descriptor":"TestSample","amount":' . CurrencyTest::getJson() . ',"legal_text":"TestSample","funding_detail":' . FundingDetailTest::getJson() . ',"additional_text":"TestSample","extends":' . FundingInstrumentTest::getJson() . ',"links":' . LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return FundingSource
     */
    public static function getObject()
    {
        return new FundingSource(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return FundingSource
     */
    public function testSerializationDeserialization()
    {
        $obj = new FundingSource(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getFundingMode());
        $this->assertNotNull($obj->getFundingInstrumentType());
        $this->assertNotNull($obj->getSoftDescriptor());
        $this->assertNotNull($obj->getAmount());
        $this->assertNotNull($obj->getLegalText());
        $this->assertNotNull($obj->getFundingDetail());
        $this->assertNotNull($obj->getAdditionalText());
        $this->assertNotNull($obj->getExtends());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param FundingSource $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getFundingMode(), "TestSample");
        $this->assertEquals($obj->getFundingInstrumentType(), "TestSample");
        $this->assertEquals($obj->getSoftDescriptor(), "TestSample");
        $this->assertEquals($obj->getAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getLegalText(), "TestSample");
        $this->assertEquals($obj->getFundingDetail(), FundingDetailTest::getObject());
        $this->assertEquals($obj->getAdditionalText(), "TestSample");
        $this->assertEquals($obj->getExtends(), FundingInstrumentTest::getObject());
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }


}
