<?php

namespace PayPal\Test\Api;

use PayPal\Api\FundingInstrument;

/**
 * Class FundingInstrument
 *
 * @package PayPal\Test\Api
 */
class FundingInstrumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object FundingInstrument
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"credit_card":' . CreditCardTest::getJson() . ',"credit_card_token":' . CreditCardTokenTest::getJson() . ',"payment_card":' . PaymentCardTest::getJson() . ',"bank_account":' . ExtendedBankAccountTest::getJson() . ',"bank_account_token":' . BankTokenTest::getJson() . ',"credit":' . CreditTest::getJson() . ',"incentive":' . IncentiveTest::getJson() . ',"external_funding":' . ExternalFundingTest::getJson() . ',"carrier_account_token":' . CarrierAccountTokenTest::getJson() . ',"carrier_account":' . CarrierAccountTest::getJson() . ',"private_label_card":' . PrivateLabelCardTest::getJson() . ',"billing":' . BillingTest::getJson() . ',"alternate_payment":' . AlternatePaymentTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return FundingInstrument
     */
    public static function getObject()
    {
        return new FundingInstrument(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return FundingInstrument
     */
    public function testSerializationDeserialization()
    {
        $obj = new FundingInstrument(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getCreditCard());
        $this->assertNotNull($obj->getCreditCardToken());
        $this->assertNotNull($obj->getPaymentCard());
        $this->assertNotNull($obj->getBankAccount());
        $this->assertNotNull($obj->getBankAccountToken());
        $this->assertNotNull($obj->getCredit());
        $this->assertNotNull($obj->getIncentive());
        $this->assertNotNull($obj->getExternalFunding());
        $this->assertNotNull($obj->getCarrierAccountToken());
        $this->assertNotNull($obj->getCarrierAccount());
        $this->assertNotNull($obj->getPrivateLabelCard());
        $this->assertNotNull($obj->getBilling());
        $this->assertNotNull($obj->getAlternatePayment());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param FundingInstrument $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getCreditCard(), CreditCardTest::getObject());
        $this->assertEquals($obj->getCreditCardToken(), CreditCardTokenTest::getObject());
        $this->assertEquals($obj->getPaymentCard(), PaymentCardTest::getObject());
        $this->assertEquals($obj->getBankAccount(), ExtendedBankAccountTest::getObject());
        $this->assertEquals($obj->getBankAccountToken(), BankTokenTest::getObject());
        $this->assertEquals($obj->getCredit(), CreditTest::getObject());
        $this->assertEquals($obj->getIncentive(), IncentiveTest::getObject());
        $this->assertEquals($obj->getExternalFunding(), ExternalFundingTest::getObject());
        $this->assertEquals($obj->getCarrierAccountToken(), CarrierAccountTokenTest::getObject());
        $this->assertEquals($obj->getCarrierAccount(), CarrierAccountTest::getObject());
        $this->assertEquals($obj->getPrivateLabelCard(), PrivateLabelCardTest::getObject());
        $this->assertEquals($obj->getBilling(), BillingTest::getObject());
        $this->assertEquals($obj->getAlternatePayment(), AlternatePaymentTest::getObject());
    }


}
