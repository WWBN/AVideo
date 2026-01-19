<?php

namespace PayPal\Test\Api;

use PayPal\Api\Invoice;

/**
 * Class Invoice
 *
 * @package PayPal\Test\Api
 */
class InvoiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Invoice
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","number":"TestSample","uri":"TestSample","status":"TestSample","merchant_info":' .MerchantInfoTest::getJson() . ',"billing_info":' .BillingInfoTest::getJson() . ',"shipping_info":' .ShippingInfoTest::getJson() . ',"items":' .InvoiceItemTest::getJson() . ',"invoice_date":"TestSample","payment_term":' .PaymentTermTest::getJson() . ',"discount":' .CostTest::getJson() . ',"shipping_cost":' .ShippingCostTest::getJson() . ',"custom":' .CustomAmountTest::getJson() . ',"tax_calculated_after_discount":true,"tax_inclusive":true,"terms":"TestSample","note":"TestSample","merchant_memo":"TestSample","logo_url":"http://www.google.com","total_amount":' .CurrencyTest::getJson() . ',"payments":' .PaymentDetailTest::getJson() . ',"refunds":' .RefundDetailTest::getJson() . ',"metadata":' .MetadataTest::getJson() . ',"additional_data":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return Invoice
     */
    public static function getObject()
    {
        return new Invoice(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return Invoice
     */
    public function testSerializationDeserialization()
    {
        $obj = new Invoice(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getNumber());
        $this->assertNotNull($obj->getUri());
        $this->assertNotNull($obj->getStatus());
        $this->assertNotNull($obj->getMerchantInfo());
        $this->assertNotNull($obj->getBillingInfo());
        $this->assertNotNull($obj->getShippingInfo());
        $this->assertNotNull($obj->getItems());
        $this->assertNotNull($obj->getInvoiceDate());
        $this->assertNotNull($obj->getPaymentTerm());
        $this->assertNotNull($obj->getDiscount());
        $this->assertNotNull($obj->getShippingCost());
        $this->assertNotNull($obj->getCustom());
        $this->assertNotNull($obj->getTaxCalculatedAfterDiscount());
        $this->assertNotNull($obj->getTaxInclusive());
        $this->assertNotNull($obj->getTerms());
        $this->assertNotNull($obj->getNote());
        $this->assertNotNull($obj->getMerchantMemo());
        $this->assertNotNull($obj->getLogoUrl());
        $this->assertNotNull($obj->getTotalAmount());
        $this->assertNotNull($obj->getPayments());
        $this->assertNotNull($obj->getRefunds());
        $this->assertNotNull($obj->getMetadata());
        $this->assertNotNull($obj->getAdditionalData());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Invoice $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getNumber(), "TestSample");
        $this->assertEquals($obj->getUri(), "TestSample");
        $this->assertEquals($obj->getStatus(), "TestSample");
        $this->assertEquals($obj->getMerchantInfo(), MerchantInfoTest::getObject());
        $this->assertEquals($obj->getBillingInfo(), BillingInfoTest::getObject());
        $this->assertEquals($obj->getShippingInfo(), ShippingInfoTest::getObject());
        $this->assertEquals($obj->getItems(), InvoiceItemTest::getObject());
        $this->assertEquals($obj->getInvoiceDate(), "TestSample");
        $this->assertEquals($obj->getPaymentTerm(), PaymentTermTest::getObject());
        $this->assertEquals($obj->getDiscount(), CostTest::getObject());
        $this->assertEquals($obj->getShippingCost(), ShippingCostTest::getObject());
        $this->assertEquals($obj->getCustom(), CustomAmountTest::getObject());
        $this->assertEquals($obj->getTaxCalculatedAfterDiscount(), true);
        $this->assertEquals($obj->getTaxInclusive(), true);
        $this->assertEquals($obj->getTerms(), "TestSample");
        $this->assertEquals($obj->getNote(), "TestSample");
        $this->assertEquals($obj->getMerchantMemo(), "TestSample");
        $this->assertEquals($obj->getLogoUrl(), "http://www.google.com");
        $this->assertEquals($obj->getTotalAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getPayments(), PaymentDetailTest::getObject());
        $this->assertEquals($obj->getRefunds(), RefundDetailTest::getObject());
        $this->assertEquals($obj->getMetadata(), MetadataTest::getObject());
        $this->assertEquals($obj->getAdditionalData(), "TestSample");
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage LogoUrl is not a fully qualified URL
     */
    public function testUrlValidationForLogoUrl()
    {
        $obj = new Invoice();
        $obj->setLogoUrl(null);
    }

    /**
     * @dataProvider mockProvider
     * @param Invoice $obj
     */
    public function testCreate($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    self::getJson()
            ));

        $result = $obj->create($mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Invoice $obj
     */
    public function testSearch($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    InvoiceSearchResponseTest::getJson()
            ));
        $search = SearchTest::getObject();

        $result = $obj->search($search, $mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Invoice $obj
     */
    public function testSend($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    true
            ));

        $result = $obj->send($mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Invoice $obj
     */
    public function testRemind($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    true
            ));
        $notification = NotificationTest::getObject();

        $result = $obj->remind($notification, $mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Invoice $obj
     */
    public function testCancel($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    true
            ));
        $cancelNotification = CancelNotificationTest::getObject();

        $result = $obj->cancel($cancelNotification, $mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Invoice $obj
     */
    public function testRecordPayment($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    true
            ));
        $paymentDetail = PaymentDetailTest::getObject();

        $result = $obj->recordPayment($paymentDetail, $mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Invoice $obj
     */
    public function testRecordRefund($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    true
            ));
        $refundDetail = RefundDetailTest::getObject();

        $result = $obj->recordRefund($refundDetail, $mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Invoice $obj
     */
    public function testGet($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    InvoiceTest::getJson()
            ));

        $result = $obj->get("invoiceId", $mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Invoice $obj
     */
    public function testGetAll($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    InvoiceSearchResponseTest::getJson()
            ));

        $result = $obj->getAll(array(), $mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Invoice $obj
     */
    public function testUpdate($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    self::getJson()
            ));

        $result = $obj->update($mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Invoice $obj
     */
    public function testDelete($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    true
            ));

        $result = $obj->delete($mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Invoice $obj
     */
    public function testQrCode($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    ImageTest::getJson()
            ));

        $result = $obj->qrCode("invoiceId", array(), $mockApiContext, $mockPayPalRestCall);
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
