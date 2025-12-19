<?php

namespace PayPal\Test\Api;

use PayPal\Api\CreditFinancingOffered;

/**
 * Class CreditFinancingOffered
 *
 * @package PayPal\Test\Api
 */
class CreditFinancingOfferedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object CreditFinancingOffered
     * @return string
     */
    public static function getJson()
    {
        return '{"total_cost":' .CurrencyTest::getJson() . ',"term":"12.34","monthly_payment":' .CurrencyTest::getJson() . ',"total_interest":' .CurrencyTest::getJson() . ',"payer_acceptance":true,"cart_amount_immutable":true}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return CreditFinancingOffered
     */
    public static function getObject()
    {
        return new CreditFinancingOffered(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return CreditFinancingOffered
     */
    public function testSerializationDeserialization()
    {
        $obj = new CreditFinancingOffered(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getTotalCost());
        $this->assertNotNull($obj->getTerm());
        $this->assertNotNull($obj->getMonthlyPayment());
        $this->assertNotNull($obj->getTotalInterest());
        $this->assertNotNull($obj->getPayerAcceptance());
        $this->assertNotNull($obj->getCartAmountImmutable());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param CreditFinancingOffered $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getTotalCost(), CurrencyTest::getObject());
        $this->assertEquals($obj->getTerm(), "12.34");
        $this->assertEquals($obj->getMonthlyPayment(), CurrencyTest::getObject());
        $this->assertEquals($obj->getTotalInterest(), CurrencyTest::getObject());
        $this->assertEquals($obj->getPayerAcceptance(), true);
        $this->assertEquals($obj->getCartAmountImmutable(), true);
    }


}
