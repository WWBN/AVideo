<?php

namespace PayPal\Test\Api;

use PayPal\Api\AgreementTransaction;

/**
 * Class AgreementTransaction
 *
 * @package PayPal\Test\Api
 */
class AgreementTransactionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object AgreementTransaction
     * @return string
     */
    public static function getJson()
    {
        return '{"transaction_id":"TestSample","status":"TestSample","transaction_type":"TestSample","amount":' .CurrencyTest::getJson() . ',"fee_amount":' .CurrencyTest::getJson() . ',"net_amount":' .CurrencyTest::getJson() . ',"payer_email":"TestSample","payer_name":"TestSample","time_stamp":"TestSample","time_zone":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return AgreementTransaction
     */
    public static function getObject()
    {
        return new AgreementTransaction(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return AgreementTransaction
     */
    public function testSerializationDeserialization()
    {
        $obj = new AgreementTransaction(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getTransactionId());
        $this->assertNotNull($obj->getStatus());
        $this->assertNotNull($obj->getTransactionType());
        $this->assertNotNull($obj->getAmount());
        $this->assertNotNull($obj->getFeeAmount());
        $this->assertNotNull($obj->getNetAmount());
        $this->assertNotNull($obj->getPayerEmail());
        $this->assertNotNull($obj->getPayerName());
        $this->assertNotNull($obj->getTimeStamp());
        $this->assertNotNull($obj->getTimeZone());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param AgreementTransaction $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getTransactionId(), "TestSample");
        $this->assertEquals($obj->getStatus(), "TestSample");
        $this->assertEquals($obj->getTransactionType(), "TestSample");
        $this->assertEquals($obj->getAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getFeeAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getNetAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getPayerEmail(), "TestSample");
        $this->assertEquals($obj->getPayerName(), "TestSample");
        $this->assertEquals($obj->getTimeStamp(), "TestSample");
        $this->assertEquals($obj->getTimeZone(), "TestSample");
    }

}
