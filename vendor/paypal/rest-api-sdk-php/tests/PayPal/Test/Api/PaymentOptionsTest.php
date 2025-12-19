<?php

namespace PayPal\Test\Api;

use PayPal\Api\PaymentOptions;

/**
 * Class PaymentOptions
 *
 * @package PayPal\Test\Api
 */
class PaymentOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PaymentOptions
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"allowed_payment_method":"TestSample","recurring_flag":true,"skip_fmf":true}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return PaymentOptions
     */
    public static function getObject()
    {
        return new PaymentOptions(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return PaymentOptions
     */
    public function testSerializationDeserialization()
    {
        $obj = new PaymentOptions(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getAllowedPaymentMethod());
        $this->assertNotNull($obj->getRecurringFlag());
        $this->assertNotNull($obj->getSkipFmf());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PaymentOptions $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getAllowedPaymentMethod(), "TestSample");
        $this->assertEquals($obj->getRecurringFlag(), true);
        $this->assertEquals($obj->getSkipFmf(), true);
    }


}
