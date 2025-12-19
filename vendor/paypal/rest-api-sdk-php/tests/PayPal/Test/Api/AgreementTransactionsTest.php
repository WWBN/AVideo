<?php

namespace PayPal\Test\Api;

use PayPal\Api\AgreementTransactions;

/**
 * Class AgreementTransactions
 *
 * @package PayPal\Test\Api
 */
class AgreementTransactionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object AgreementTransactions
     * @return string
     */
    public static function getJson()
    {
        return '{"agreement_transaction_list":' .AgreementTransactionTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return AgreementTransactions
     */
    public static function getObject()
    {
        return new AgreementTransactions(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return AgreementTransactions
     */
    public function testSerializationDeserialization()
    {
        $obj = new AgreementTransactions(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getAgreementTransactionList());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param AgreementTransactions $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getAgreementTransactionList(), AgreementTransactionTest::getObject());
    }

}
