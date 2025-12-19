<?php

namespace PayPal\Test\Api;

use PayPal\Api\Metadata;

/**
 * Class Metadata
 *
 * @package PayPal\Test\Api
 */
class MetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Metadata
     * @return string
     */
    public static function getJson()
    {
        return '{"created_date":"TestSample","created_by":"TestSample","cancelled_date":"TestSample","cancelled_by":"TestSample","last_updated_date":"TestSample","last_updated_by":"TestSample","first_sent_date":"TestSample","last_sent_date":"TestSample","last_sent_by":"TestSample","payer_view_url":"http://www.google.com"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return Metadata
     */
    public static function getObject()
    {
        return new Metadata(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return Metadata
     */
    public function testSerializationDeserialization()
    {
        $obj = new Metadata(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getCreatedDate());
        $this->assertNotNull($obj->getCreatedBy());
        $this->assertNotNull($obj->getCancelledDate());
        $this->assertNotNull($obj->getCancelledBy());
        $this->assertNotNull($obj->getLastUpdatedDate());
        $this->assertNotNull($obj->getLastUpdatedBy());
        $this->assertNotNull($obj->getFirstSentDate());
        $this->assertNotNull($obj->getLastSentDate());
        $this->assertNotNull($obj->getLastSentBy());
        $this->assertNotNull($obj->getPayerViewUrl());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Metadata $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getCreatedDate(), "TestSample");
        $this->assertEquals($obj->getCreatedBy(), "TestSample");
        $this->assertEquals($obj->getCancelledDate(), "TestSample");
        $this->assertEquals($obj->getCancelledBy(), "TestSample");
        $this->assertEquals($obj->getLastUpdatedDate(), "TestSample");
        $this->assertEquals($obj->getLastUpdatedBy(), "TestSample");
        $this->assertEquals($obj->getFirstSentDate(), "TestSample");
        $this->assertEquals($obj->getLastSentDate(), "TestSample");
        $this->assertEquals($obj->getLastSentBy(), "TestSample");
        $this->assertEquals($obj->getPayerViewUrl(), "http://www.google.com");
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage PayerViewUrl is not a fully qualified URL
     */
    public function testUrlValidationForPayerViewUrl()
    {
        $obj = new Metadata();
        $obj->setPayerViewUrl(null);
    }

    public function testUrlValidationForPayerViewUrlDeprecated()
    {
        $obj = new Metadata();
        $obj->setPayer_view_url(null);
        $this->assertNull($obj->getPayer_view_url());
    }

}
