<?php

namespace PayPal\Test\Api;

use PayPal\Api\FlowConfig;

/**
 * Class FlowConfig
 *
 * @package PayPal\Test\Api
 */
class FlowConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object FlowConfig
     * @return string
     */
    public static function getJson()
    {
        return '{"landing_page_type":"TestSample","bank_txn_pending_url":"http://www.google.com"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return FlowConfig
     */
    public static function getObject()
    {
        return new FlowConfig(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return FlowConfig
     */
    public function testSerializationDeserialization()
    {
        $obj = new FlowConfig(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getLandingPageType());
        $this->assertNotNull($obj->getBankTxnPendingUrl());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param FlowConfig $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getLandingPageType(), "TestSample");
        $this->assertEquals($obj->getBankTxnPendingUrl(), "http://www.google.com");
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage BankTxnPendingUrl is not a fully qualified URL
     */
    public function testUrlValidationForBankTxnPendingUrl()
    {
        $obj = new FlowConfig();
        $obj->setBankTxnPendingUrl(null);
    }

}
