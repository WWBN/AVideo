<?php

namespace PayPal\Test\Api;

use PayPal\Api\CreditCardToken;

/**
 * Class CreditCardToken
 *
 * @package PayPal\Test\Api
 */
class CreditCardTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object CreditCardToken
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"credit_card_id":"TestSample","payer_id":"TestSample","last4":"TestSample","type":"TestSample","expire_month":123,"expire_year":123}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return CreditCardToken
     */
    public static function getObject()
    {
        return new CreditCardToken(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return CreditCardToken
     */
    public function testSerializationDeserialization()
    {
        $obj = new CreditCardToken(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getCreditCardId());
        $this->assertNotNull($obj->getPayerId());
        $this->assertNotNull($obj->getLast4());
        $this->assertNotNull($obj->getType());
        $this->assertNotNull($obj->getExpireMonth());
        $this->assertNotNull($obj->getExpireYear());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param CreditCardToken $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getCreditCardId(), "TestSample");
        $this->assertEquals($obj->getPayerId(), "TestSample");
        $this->assertEquals($obj->getLast4(), "TestSample");
        $this->assertEquals($obj->getType(), "TestSample");
        $this->assertEquals($obj->getExpireMonth(), 123);
        $this->assertEquals($obj->getExpireYear(), 123);
    }


}
