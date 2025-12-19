<?php

namespace PayPal\Test\Api;

use PayPal\Api\Billing;

/**
 * Class Billing
 *
 * @package PayPal\Test\Api
 */
class BillingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Billing
     * @return string
     */
    public static function getJson()
    {
        return '{"billing_agreement_id":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return Billing
     */
    public static function getObject()
    {
        return new Billing(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return Billing
     */
    public function testSerializationDeserialization()
    {
        $obj = new Billing(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getBillingAgreementId());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Billing $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getBillingAgreementId(), "TestSample");
    }


}
