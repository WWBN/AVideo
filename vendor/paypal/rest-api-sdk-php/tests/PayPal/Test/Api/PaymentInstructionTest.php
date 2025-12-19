<?php

namespace PayPal\Test\Api;

use PayPal\Api\PaymentInstruction;
use PayPal\Transport\PPRestCall;

/**
 * Class PaymentInstruction
 *
 * @package PayPal\Test\Api
 */
class PaymentInstructionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PaymentInstruction
     * @return string
     */
    public static function getJson()
    {
        return '{"reference_number":"TestSample","instruction_type":"TestSample","recipient_banking_instruction":' .RecipientBankingInstructionTest::getJson() . ',"amount":' .CurrencyTest::getJson() . ',"payment_due_date":"TestSample","note":"TestSample","links":' .LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return PaymentInstruction
     */
    public static function getObject()
    {
        return new PaymentInstruction(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return PaymentInstruction
     */
    public function testSerializationDeserialization()
    {
        $obj = new PaymentInstruction(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getReferenceNumber());
        $this->assertNotNull($obj->getInstructionType());
        $this->assertNotNull($obj->getRecipientBankingInstruction());
        $this->assertNotNull($obj->getAmount());
        $this->assertNotNull($obj->getPaymentDueDate());
        $this->assertNotNull($obj->getNote());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PaymentInstruction $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getReferenceNumber(), "TestSample");
        $this->assertEquals($obj->getInstructionType(), "TestSample");
        $this->assertEquals($obj->getRecipientBankingInstruction(), RecipientBankingInstructionTest::getObject());
        $this->assertEquals($obj->getAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getPaymentDueDate(), "TestSample");
        $this->assertEquals($obj->getNote(), "TestSample");
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

    /**
     * @dataProvider mockProvider
     * @param PaymentInstruction $obj
     */
    public function testGet($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    PaymentInstructionTest::getJson()
            ));

        $result = $obj->get("paymentId", $mockApiContext, $mockPPRestCall);
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
