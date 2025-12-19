<?php

namespace PayPal\Test\Api;

use PayPal\Api\Sale;

/**
 * Class Sale
 *
 * @package PayPal\Test\Api
 */
class SaleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Sale
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","purchase_unit_reference_id":"TestSample","amount":' . AmountTest::getJson() . ',"payment_mode":"TestSample","state":"TestSample","reason_code":"TestSample","protection_eligibility":"TestSample","protection_eligibility_type":"TestSample","clearing_time":"TestSample","payment_hold_status":"TestSample","payment_hold_reasons":"TestSample","transaction_fee":' . CurrencyTest::getJson() . ',"receivable_amount":' . CurrencyTest::getJson() . ',"exchange_rate":"TestSample","fmf_details":' . FmfDetailsTest::getJson() . ',"receipt_id":"TestSample","parent_payment":"TestSample","processor_response":' . ProcessorResponseTest::getJson() . ',"billing_agreement_id":"TestSample","create_time":"TestSample","update_time":"TestSample","links":' . LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Sale
     */
    public static function getObject()
    {
        return new Sale(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Sale
     */
    public function testSerializationDeserialization()
    {
        $obj = new Sale(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getPurchaseUnitReferenceId());
        $this->assertNotNull($obj->getAmount());
        $this->assertNotNull($obj->getPaymentMode());
        $this->assertNotNull($obj->getState());
        $this->assertNotNull($obj->getReasonCode());
        $this->assertNotNull($obj->getProtectionEligibility());
        $this->assertNotNull($obj->getProtectionEligibilityType());
        $this->assertNotNull($obj->getClearingTime());
        $this->assertNotNull($obj->getPaymentHoldStatus());
        $this->assertNotNull($obj->getPaymentHoldReasons());
        $this->assertNotNull($obj->getTransactionFee());
        $this->assertNotNull($obj->getReceivableAmount());
        $this->assertNotNull($obj->getExchangeRate());
        $this->assertNotNull($obj->getFmfDetails());
        $this->assertNotNull($obj->getReceiptId());
        $this->assertNotNull($obj->getParentPayment());
        $this->assertNotNull($obj->getProcessorResponse());
        $this->assertNotNull($obj->getBillingAgreementId());
        $this->assertNotNull($obj->getCreateTime());
        $this->assertNotNull($obj->getUpdateTime());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Sale $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getPurchaseUnitReferenceId(), "TestSample");
        $this->assertEquals($obj->getAmount(), AmountTest::getObject());
        $this->assertEquals($obj->getPaymentMode(), "TestSample");
        $this->assertEquals($obj->getState(), "TestSample");
        $this->assertEquals($obj->getReasonCode(), "TestSample");
        $this->assertEquals($obj->getProtectionEligibility(), "TestSample");
        $this->assertEquals($obj->getProtectionEligibilityType(), "TestSample");
        $this->assertEquals($obj->getClearingTime(), "TestSample");
        $this->assertEquals($obj->getPaymentHoldStatus(), "TestSample");
        $this->assertEquals($obj->getPaymentHoldReasons(), "TestSample");
        $this->assertEquals($obj->getTransactionFee(), CurrencyTest::getObject());
        $this->assertEquals($obj->getReceivableAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getExchangeRate(), "TestSample");
        $this->assertEquals($obj->getFmfDetails(), FmfDetailsTest::getObject());
        $this->assertEquals($obj->getReceiptId(), "TestSample");
        $this->assertEquals($obj->getParentPayment(), "TestSample");
        $this->assertEquals($obj->getProcessorResponse(), ProcessorResponseTest::getObject());
        $this->assertEquals($obj->getBillingAgreementId(), "TestSample");
        $this->assertEquals($obj->getCreateTime(), "TestSample");
        $this->assertEquals($obj->getUpdateTime(), "TestSample");
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

    /**
     * @dataProvider mockProvider
     * @param Sale $obj
     */
    public function testGet($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                SaleTest::getJson()
            ));

        $result = $obj->get("saleId", $mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider mockProvider
     * @param Sale $obj
     */
    public function testRefund($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                RefundTest::getJson()
            ));
        $refund = RefundTest::getObject();

        $result = $obj->refund($refund, $mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }

    public function mockProvider()
    {
        $obj = self::getObject();
        $mockApiContext = $this->getMockBuilder('ApiContext')
            ->disableOriginalConstructor()
            ->getMock();
        return array(
            array($obj, $mockApiContext),
            array($obj, null)
        );
    }
}
