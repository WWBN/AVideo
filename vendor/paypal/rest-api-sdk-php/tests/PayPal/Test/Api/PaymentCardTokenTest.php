<?php

namespace PayPal\Test\Api;

use PayPal\Api\PaymentCardToken;

/**
 * Class PaymentCardToken
 *
 * @package PayPal\Test\Api
 */
class PaymentCardTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PaymentCardToken
     * @return string
     */
    public static function getJson()
    {
        return '{"payment_card_id":"TestSample","external_customer_id":"TestSample","last4":"TestSample","type":"TestSample","expire_month":123,"expire_year":123}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return PaymentCardToken
     */
    public static function getObject()
    {
        return new PaymentCardToken(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return PaymentCardToken
     */
    public function testSerializationDeserialization()
    {
        $obj = new PaymentCardToken(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getPaymentCardId());
        $this->assertNotNull($obj->getExternalCustomerId());
        $this->assertNotNull($obj->getLast4());
        $this->assertNotNull($obj->getType());
        $this->assertNotNull($obj->getExpireMonth());
        $this->assertNotNull($obj->getExpireYear());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PaymentCardToken $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getPaymentCardId(), "TestSample");
        $this->assertEquals($obj->getExternalCustomerId(), "TestSample");
        $this->assertEquals($obj->getLast4(), "TestSample");
        $this->assertEquals($obj->getType(), "TestSample");
        $this->assertEquals($obj->getExpireMonth(), 123);
        $this->assertEquals($obj->getExpireYear(), 123);
    }

}
