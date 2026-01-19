<?php

namespace PayPal\Test\Api;

use PayPal\Api\PrivateLabelCard;

/**
 * Class PrivateLabelCard
 *
 * @package PayPal\Test\Api
 */
class PrivateLabelCardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PrivateLabelCard
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","card_number":"TestSample","issuer_id":"TestSample","issuer_name":"TestSample","image_key":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return PrivateLabelCard
     */
    public static function getObject()
    {
        return new PrivateLabelCard(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return PrivateLabelCard
     */
    public function testSerializationDeserialization()
    {
        $obj = new PrivateLabelCard(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getCardNumber());
        $this->assertNotNull($obj->getIssuerId());
        $this->assertNotNull($obj->getIssuerName());
        $this->assertNotNull($obj->getImageKey());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PrivateLabelCard $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getCardNumber(), "TestSample");
        $this->assertEquals($obj->getIssuerId(), "TestSample");
        $this->assertEquals($obj->getIssuerName(), "TestSample");
        $this->assertEquals($obj->getImageKey(), "TestSample");
    }


}
