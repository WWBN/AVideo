<?php

namespace PayPal\Test\Api;

use PayPal\Api\WebhookEventTypeList;

/**
 * Class WebhookEventTypeList
 *
 * @package PayPal\Test\Api
 */
class WebhookEventTypeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object WebhookEventTypeList
     * @return string
     */
    public static function getJson()
    {
        return '{"event_types":' .WebhookEventTypeTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return WebhookEventTypeList
     */
    public static function getObject()
    {
        return new WebhookEventTypeList(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return WebhookEventTypeList
     */
    public function testSerializationDeserialization()
    {
        $obj = new WebhookEventTypeList(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getEventTypes());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param WebhookEventTypeList $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getEventTypes(), WebhookEventTypeTest::getObject());
    }

}
