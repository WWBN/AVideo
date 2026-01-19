<?php

namespace PayPal\Test\Functional\Api;

use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Webhook;
use PayPal\Api\WebhookEvent;
use PayPal\Api\WebhookEventType;
use PayPal\Api\WebhookEventTypeList;
use PayPal\Api\WebhookList;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Test\Functional\Setup;

/**
 * Class WebhookFunctionalTest
 *
 * @package PayPal\Test\Api
 */
class WebhookFunctionalTest extends \PHPUnit_Framework_TestCase
{

    public $operation;

    public $response;

    public $mockPayPalRestCall;

    public $apiContext;

    public function setUp()
    {
        $className = $this->getClassName();
        $testName = $this->getName();
        $operationString = file_get_contents(__DIR__ . "/../resources/$className/$testName.json");
        $this->operation = json_decode($operationString, true);
        $this->response = true;
        if (array_key_exists('body', $this->operation['response'])) {
            $this->response = json_encode($this->operation['response']['body']);
        }
        Setup::SetUpForFunctionalTests($this);
    }

    /**
     * Returns just the classname of the test you are executing. It removes the namespaces.
     * @return string
     */
    public function getClassName()
    {
        return join('', array_slice(explode('\\', get_class($this)), -1));
    }

    public function testCreate()
    {
        $request = $this->operation['request']['body'];
        $obj = new Webhook($request);
        // Adding a random url request to make it unique
        $obj->setUrl($obj->getUrl() . '?rand=' . uniqid());
        $result = null;
        try {
            $result = $obj->create($this->apiContext, $this->mockPayPalRestCall);
        } catch (PayPalConnectionException $ex) {
            $data = $ex->getData();
            if (strpos($data,'WEBHOOK_NUMBER_LIMIT_EXCEEDED') !== false) {
                $this->deleteAll();
                $result = $obj->create($this->apiContext, $this->mockPayPalRestCall);
            } else {
                $this->fail($ex->getMessage());
            }
        }
        $this->assertNotNull($result);
        return $result;
    }

    public function deleteAll()
    {
        $result = Webhook::getAll($this->apiContext, $this->mockPayPalRestCall);
        foreach ($result->getWebhooks() as $webhookObject) {
            $webhookObject->delete($this->apiContext, $this->mockPayPalRestCall);
        }
    }

    /**
     * @depends testCreate
     * @param $webhook Webhook
     * @return Webhook
     */
    public function testGet($webhook)
    {
        $result = Webhook::get($webhook->getId(), $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        $this->assertEquals($webhook->getId(), $result->getId());
        $this->assertEquals($webhook, $result, "", 0, 10, true);
        return $result;
    }

    /**
     * @depends testGet
     * @param $webhook Webhook
     * @return WebhookEventTypeList
     */
    public function testGetSubscribedEventTypes($webhook)
    {
        $result = WebhookEventType::subscribedEventTypes($webhook->getId(), $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        $this->assertEquals(2, sizeof($result->getEventTypes()));
        return $result;
    }

    /**
     * @depends testGet
     * @param $webhook Webhook
     * @return WebhookList
     */
    public function testGetAll($webhook)
    {
        $result = Webhook::getAll($this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        $found = false;
        $foundObject = null;
        foreach ($result->getWebhooks() as $webhookObject) {
            if ($webhookObject->getId() == $webhook->getId()) {
                $found = true;
                $foundObject = $webhookObject;
                break;
            }
        }
        $this->assertTrue($found, "The Created Webhook was not found in the get list");
        $this->assertEquals($webhook->getId(), $foundObject->getId());
        return $result;
    }

    /**
     * @depends testGet
     * @param $webhook Webhook
     */
    public function testUpdate($webhook)
    {
        $patches = array();
        foreach ($this->operation['request']['body'] as $request) {
            /** @var Patch[] $request */
            $patch = new Patch();
            $patch->setOp($request['op']);
            $patch->setPath($request['path']);
            $patch->setValue($request['value']);
            if ($request['path'] == "/url") {
                $new_url = $request['value'] . '?rand=' .uniqid();
                $patch->setValue($new_url);
            }
            $patches[] = $patch;
        }

        $patchRequest = new PatchRequest();
        $patchRequest->setPatches($patches);
        $result = $webhook->update($patchRequest, $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        $found = false;
        $foundObject = null;
        foreach ($result->getEventTypes() as $eventType) {
            if ($eventType->getName() == "PAYMENT.SALE.REFUNDED") {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * @depends testGet
     * @param $webhook Webhook
     */
    public function testDelete($webhook)
    {
        $result = $webhook->delete($this->apiContext, $this->mockPayPalRestCall);
        $this->assertTrue($result);
    }

    public function testEventSearch()
    {
        $result = WebhookEvent::all(array(),$this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        return $result;
    }
}
