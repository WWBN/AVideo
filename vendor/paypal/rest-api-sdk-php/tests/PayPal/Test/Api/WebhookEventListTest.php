<?php

namespace PayPal\Test\Api;

use PayPal\Api\WebhookEventList;

/**
 * Class WebhookEventList
 *
 * @package PayPal\Test\Api
 */
class WebhookEventListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object WebhookEventList
     * @return string
     */
    public static function getJson()
    {
        return '{"events":' .WebhookEventTest::getJson() . ',"count":123,"links":' .LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return WebhookEventList
     */
    public static function getObject()
    {
        return new WebhookEventList(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return WebhookEventList
     */
    public function testSerializationDeserialization()
    {
        $obj = new WebhookEventList(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getEvents());
        $this->assertNotNull($obj->getCount());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param WebhookEventList $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getEvents(), WebhookEventTest::getObject());
        $this->assertEquals($obj->getCount(), 123);
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

}
