<?php

namespace PayPal\Test\Api;

use PayPal\Api\ExternalFunding;

/**
 * Class ExternalFunding
 *
 * @package PayPal\Test\Api
 */
class ExternalFundingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object ExternalFunding
     * @return string
     */
    public static function getJson()
    {
        return '{"reference_id":"TestSample","code":"TestSample","funding_account_id":"TestSample","display_text":"TestSample","amount":' .AmountTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return ExternalFunding
     */
    public static function getObject()
    {
        return new ExternalFunding(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return ExternalFunding
     */
    public function testSerializationDeserialization()
    {
        $obj = new ExternalFunding(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getReferenceId());
        $this->assertNotNull($obj->getCode());
        $this->assertNotNull($obj->getFundingAccountId());
        $this->assertNotNull($obj->getDisplayText());
        $this->assertNotNull($obj->getAmount());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param ExternalFunding $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getReferenceId(), "TestSample");
        $this->assertEquals($obj->getCode(), "TestSample");
        $this->assertEquals($obj->getFundingAccountId(), "TestSample");
        $this->assertEquals($obj->getDisplayText(), "TestSample");
        $this->assertEquals($obj->getAmount(), AmountTest::getObject());
    }


}
