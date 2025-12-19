<?php

namespace PayPal\Test\Api;

use PayPal\Api\WebhookEvent;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

/**
 * Class WebhookEvent
 *
 * @package PayPal\Test\Api
 */
class WebhookEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object WebhookEvent
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","create_time":"TestSample","resource_type":"TestSample","event_type":"TestSample","summary":"TestSample","resource":"TestSampleObject","links":' .LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return WebhookEvent
     */
    public static function getObject()
    {
        return new WebhookEvent(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return WebhookEvent
     */
    public function testSerializationDeserialization()
    {
        $obj = new WebhookEvent(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getCreateTime());
        $this->assertNotNull($obj->getResourceType());
        $this->assertNotNull($obj->getEventType());
        $this->assertNotNull($obj->getSummary());
        $this->assertNotNull($obj->getResource());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param WebhookEvent $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getCreateTime(), "TestSample");
        $this->assertEquals($obj->getResourceType(), "TestSample");
        $this->assertEquals($obj->getEventType(), "TestSample");
        $this->assertEquals($obj->getSummary(), "TestSample");
        $this->assertEquals($obj->getResource(), "TestSampleObject");
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

    /**
     * @dataProvider mockProvider
     * @param WebhookEvent $obj
     */
    public function testGet($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    WebhookEventTest::getJson()
            ));

        $result = $obj->get("eventId", $mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param WebhookEvent $obj
     */
    public function testResend($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    self::getJson()
            ));

        $result = $obj->resend($mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param WebhookEvent $obj
     */
    public function testList($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    WebhookEventListTest::getJson()
            ));
        $params = array();

        $result = $obj->all($params, $mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider mockProvider
     * @param WebhookEvent $obj
     */
    public function testValidateWebhook($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                WebhookEventTest::getJson()
            ));

        $result = WebhookEvent::validateAndGetReceivedEvent('{"id":"123"}', $mockApiContext, $mockPayPalRestCall);
        //$result = $obj->get("eventId", $mockApiContext, $mockPayPalRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @dataProvider mockProvider
     * @param WebhookEvent $obj
     * @param ApiContext $mockApiContext
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Webhook Event Id provided in the data is incorrect. This could happen if anyone other than PayPal is faking the incoming webhook data.
     */
    public function testValidateWebhook404($obj, $mockApiContext)
    {
        $mockPayPalRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayPalRestCall->expects($this->any())
            ->method('execute')
            ->will($this->throwException(new PayPalConnectionException(null, "404 not found", 404)));

        $result = WebhookEvent::validateAndGetReceivedEvent('{"id":"123"}', $mockApiContext, $mockPayPalRestCall);
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

    /**
     * @dataProvider mockProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Body cannot be null or empty
     */
    public function testValidateWebhookNull($mockApiContext)
    {
        WebhookEvent::validateAndGetReceivedEvent(null, $mockApiContext);
    }

    /**
     * @dataProvider mockProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Body cannot be null or empty
     */
    public function testValidateWebhookEmpty($mockApiContext)
    {
       WebhookEvent::validateAndGetReceivedEvent('', $mockApiContext);
    }

    /**
     * @dataProvider mockProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Request Body is not a valid JSON.
     */
    public function testValidateWebhookInvalid($mockApiContext)
    {
        WebhookEvent::validateAndGetReceivedEvent('something-invalid', $mockApiContext);
    }

    /**
     * @dataProvider mockProvider
     * @param $mockApiContext ApiContext
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Id attribute not found in JSON. Possible reason could be invalid JSON Object
     */
    public function testValidateWebhookValidJSONWithoutId($obj, $mockApiContext)
    {
        WebhookEvent::validateAndGetReceivedEvent('{"summary":"json"}', $mockApiContext);
    }

}
