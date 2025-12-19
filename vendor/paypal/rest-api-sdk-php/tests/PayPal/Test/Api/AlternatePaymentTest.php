<?php

namespace PayPal\Test\Api;

use PayPal\Api\AlternatePayment;

/**
 * Class AlternatePayment
 *
 * @package PayPal\Test\Api
 */
class AlternatePaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object AlternatePayment
     * @return string
     */
    public static function getJson()
    {
        return '{"alternate_payment_account_id":"TestSample","external_customer_id":"TestSample","alternate_payment_provider_id":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return AlternatePayment
     */
    public static function getObject()
    {
        return new AlternatePayment(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return AlternatePayment
     */
    public function testSerializationDeserialization()
    {
        $obj = new AlternatePayment(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getAlternatePaymentAccountId());
        $this->assertNotNull($obj->getExternalCustomerId());
        $this->assertNotNull($obj->getAlternatePaymentProviderId());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param AlternatePayment $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getAlternatePaymentAccountId(), "TestSample");
        $this->assertEquals($obj->getExternalCustomerId(), "TestSample");
        $this->assertEquals($obj->getAlternatePaymentProviderId(), "TestSample");
    }


}
