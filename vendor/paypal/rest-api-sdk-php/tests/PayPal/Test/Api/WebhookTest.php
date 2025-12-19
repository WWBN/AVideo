<?php

namespace PayPal\Test\Api;

use PayPal\Api\Webhook;

/**
 * Class Webhook
 *
 * @package PayPal\Test\Api
 */
class WebhookTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Webhook
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","url":"http://www.google.com","event_types":' .WebhookEventTypeTest::getJson() . ',"links":' .LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return Webhook
     */
    public static function getObject()
    {
        return new Webhook(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return Webhook
     */
    public function testSerializationDeserialization()
    {
        $obj = new Webhook(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getUrl());
        $this->assertNotNull($obj->getEventTypes());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Webhook $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getUrl(), "http://www.google.com");
        $this->assertEquals($obj->getEventTypes(), WebhookEventTypeTest::getObject());
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Url is not a fully qualified URL
     */
    public function testUrlValidationForUrl()
    {
        $obj = new Webhook();
        $obj->setUrl(null);
    }

    /**
     * @dataProvider mockProvider
     * @param Webhook $obj
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
     * @param Webhook $obj
     */
    public function testGet($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    WebhookTest::getJson()
            ));

        $result = $obj->get("webhookId", $mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Webhook $obj
     */
    public function testGetAll($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    WebhookListTest::getJson()
            ));

        $result = $obj->getAll($mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Webhook $obj
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
        $patchRequest = PatchRequestTest::getObject();

        $result = $obj->update($patchRequest, $mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Webhook $obj
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
