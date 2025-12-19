<?php

namespace PayPal\Test\Api;

use PayPal\Api\PaymentTerm;

/**
 * Class PaymentTerm
 *
 * @package PayPal\Test\Api
 */
class PaymentTermTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PaymentTerm
     * @return string
     */
    public static function getJson()
    {
        return '{"term_type":"TestSample","due_date":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return PaymentTerm
     */
    public static function getObject()
    {
        return new PaymentTerm(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return PaymentTerm
     */
    public function testSerializationDeserialization()
    {
        $obj = new PaymentTerm(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getTermType());
        $this->assertNotNull($obj->getDueDate());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PaymentTerm $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getTermType(), "TestSample");
        $this->assertEquals($obj->getDueDate(), "TestSample");
    }
    
}
