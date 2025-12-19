<?php

namespace PayPal\Test\Api;

use PayPal\Api\object;
use PayPal\Api\Payment;

/**
 * Class Payment
 *
 * @package PayPal\Test\Api
 */
class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Payment
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","intent":"TestSample","payer":' . PayerTest::getJson() . ',"potential_payer_info":' . PotentialPayerInfoTest::getJson() . ',"payee":' . PayeeTest::getJson() . ',"cart":"TestSample","transactions":[' . TransactionTest::getJson() . '],"failed_transactions":' . ErrorTest::getJson() . ',"billing_agreement_tokens":["TestSample"],"credit_financing_offered":' . CreditFinancingOfferedTest::getJson() . ',"payment_instruction":' . PaymentInstructionTest::getJson() . ',"state":"TestSample","experience_profile_id":"TestSample","note_to_payer":"TestSample","redirect_urls":' . RedirectUrlsTest::getJson() . ',"failure_reason":"TestSample","create_time":"TestSample","update_time":"TestSample","links":' . LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return Payment
     */
    public static function getObject()
    {
        return new Payment(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return Payment
     */
    public function testSerializationDeserialization()
    {
        $obj = new Payment(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getIntent());
        $this->assertNotNull($obj->getPayer());
        $this->assertNotNull($obj->getPotentialPayerInfo());
        $this->assertNotNull($obj->getPayee());
        $this->assertNotNull($obj->getCart());
        $this->assertNotNull($obj->getTransactions());
        $this->assertNotNull($obj->getFailedTransactions());
        $this->assertNotNull($obj->getBillingAgreementTokens());
        $this->assertNotNull($obj->getCreditFinancingOffered());
        $this->assertNotNull($obj->getPaymentInstruction());
        $this->assertNotNull($obj->getState());
        $this->assertNotNull($obj->getExperienceProfileId());
        $this->assertNotNull($obj->getNoteToPayer());
        $this->assertNotNull($obj->getRedirectUrls());
        $this->assertNotNull($obj->getFailureReason());
        $this->assertNotNull($obj->getCreateTime());
        $this->assertNotNull($obj->getUpdateTime());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Payment $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getIntent(), "TestSample");
        $this->assertEquals($obj->getPayer(), PayerTest::getObject());
        $this->assertEquals($obj->getPotentialPayerInfo(), PotentialPayerInfoTest::getObject());
        $this->assertEquals($obj->getPayee(), PayeeTest::getObject());
        $this->assertEquals($obj->getCart(), "TestSample");
        $this->assertEquals($obj->getTransactions(), array(TransactionTest::getObject()));
        $this->assertEquals($obj->getFailedTransactions(), ErrorTest::getObject());
        $this->assertEquals($obj->getBillingAgreementTokens(), array("TestSample"));
        $this->assertEquals($obj->getCreditFinancingOffered(), CreditFinancingOfferedTest::getObject());
        $this->assertEquals($obj->getPaymentInstruction(), PaymentInstructionTest::getObject());
        $this->assertEquals($obj->getState(), "TestSample");
        $this->assertEquals($obj->getExperienceProfileId(), "TestSample");
        $this->assertEquals($obj->getNoteToPayer(), "TestSample");
        $this->assertEquals($obj->getRedirectUrls(), RedirectUrlsTest::getObject());
        $this->assertEquals($obj->getFailureReason(), "TestSample");
        $this->assertEquals($obj->getCreateTime(), "TestSample");
        $this->assertEquals($obj->getUpdateTime(), "TestSample");
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

    /**
     * @dataProvider mockProvider
     * @param Payment $obj
     */
    public function testCreate($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                self::getJson()
            ));

        $result = $obj->create($mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider mockProvider
     * @param Payment $obj
     */
    public function testGet($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                PaymentTest::getJson()
            ));

        $result = $obj->get("paymentId", $mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider mockProvider
     * @param Payment $obj
     */
    public function testUpdate($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    true
            ));
        $patchRequest = PatchRequestTest::getObject();

        $result = $obj->update($patchRequest, $mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider mockProvider
     * @param Payment $obj
     */
    public function testExecute($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                self::getJson()
            ));
        $paymentExecution = PaymentExecutionTest::getObject();

        $result = $obj->execute($paymentExecution, $mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider mockProvider
     * @param Payment $obj
     */
    public function testList($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                PaymentHistoryTest::getJson()
            ));
        $params = array();

        $result = $obj->all($params, $mockApiContext, $mockPPRestCall);
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
