<?php

namespace PayPal\Test\Api;

use PayPal\Api\Search;

/**
 * Class Search
 *
 * @package PayPal\Test\Api
 */
class SearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Search
     * @return string
     */
    public static function getJson()
    {
        return '{"email":"TestSample","recipient_first_name":"TestSample","recipient_last_name":"TestSample","recipient_business_name":"TestSample","number":"TestSample","status":"TestSample","lower_total_amount":' .CurrencyTest::getJson() . ',"upper_total_amount":' .CurrencyTest::getJson() . ',"start_invoice_date":"TestSample","end_invoice_date":"TestSample","start_due_date":"TestSample","end_due_date":"TestSample","start_payment_date":"TestSample","end_payment_date":"TestSample","start_creation_date":"TestSample","end_creation_date":"TestSample","page":"12.34","page_size":"12.34","total_count_required":true}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return Search
     */
    public static function getObject()
    {
        return new Search(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return Search
     */
    public function testSerializationDeserialization()
    {
        $obj = new Search(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getEmail());
        $this->assertNotNull($obj->getRecipientFirstName());
        $this->assertNotNull($obj->getRecipientLastName());
        $this->assertNotNull($obj->getRecipientBusinessName());
        $this->assertNotNull($obj->getNumber());
        $this->assertNotNull($obj->getStatus());
        $this->assertNotNull($obj->getLowerTotalAmount());
        $this->assertNotNull($obj->getUpperTotalAmount());
        $this->assertNotNull($obj->getStartInvoiceDate());
        $this->assertNotNull($obj->getEndInvoiceDate());
        $this->assertNotNull($obj->getStartDueDate());
        $this->assertNotNull($obj->getEndDueDate());
        $this->assertNotNull($obj->getStartPaymentDate());
        $this->assertNotNull($obj->getEndPaymentDate());
        $this->assertNotNull($obj->getStartCreationDate());
        $this->assertNotNull($obj->getEndCreationDate());
        $this->assertNotNull($obj->getPage());
        $this->assertNotNull($obj->getPageSize());
        $this->assertNotNull($obj->getTotalCountRequired());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Search $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getEmail(), "TestSample");
        $this->assertEquals($obj->getRecipientFirstName(), "TestSample");
        $this->assertEquals($obj->getRecipientLastName(), "TestSample");
        $this->assertEquals($obj->getRecipientBusinessName(), "TestSample");
        $this->assertEquals($obj->getNumber(), "TestSample");
        $this->assertEquals($obj->getStatus(), "TestSample");
        $this->assertEquals($obj->getLowerTotalAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getUpperTotalAmount(), CurrencyTest::getObject());
        $this->assertEquals($obj->getStartInvoiceDate(), "TestSample");
        $this->assertEquals($obj->getEndInvoiceDate(), "TestSample");
        $this->assertEquals($obj->getStartDueDate(), "TestSample");
        $this->assertEquals($obj->getEndDueDate(), "TestSample");
        $this->assertEquals($obj->getStartPaymentDate(), "TestSample");
        $this->assertEquals($obj->getEndPaymentDate(), "TestSample");
        $this->assertEquals($obj->getStartCreationDate(), "TestSample");
        $this->assertEquals($obj->getEndCreationDate(), "TestSample");
        $this->assertEquals($obj->getPage(), "12.34");
        $this->assertEquals($obj->getPageSize(), "12.34");
        $this->assertEquals($obj->getTotalCountRequired(), true);
    }

}
