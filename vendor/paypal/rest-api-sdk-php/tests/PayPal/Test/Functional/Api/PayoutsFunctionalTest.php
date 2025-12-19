<?php

namespace PayPal\Test\Functional\Api;

use PayPal\Api\Payout;
use PayPal\Api\PayoutBatch;
use PayPal\Api\PayoutItem;
use PayPal\Test\Functional\Setup;

/**
 * Class Payouts
 *
 * @package PayPal\Test\Api
 */
class PayoutsFunctionalTest extends \PHPUnit_Framework_TestCase
{

    public $operation;

    public $response;

    public $mockPayPalRestCall;

    public $apiContext;

    public static $batchId;

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
        $obj = new Payout($request);
        if (Setup::$mode != 'mock') {
            $obj->getSenderBatchHeader()->setSenderBatchId(uniqid());
        }
        PayoutsFunctionalTest::$batchId = $obj->getSenderBatchHeader()->getSenderBatchId();
        $params = array('sync_mode' => 'true');
        $result = $obj->create($params, $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        $this->assertEquals(PayoutsFunctionalTest::$batchId, $result->getBatchHeader()->getSenderBatchHeader()->getSenderBatchId());
        $this->assertEquals('SUCCESS', $result->getBatchHeader()->getBatchStatus());
        $items = $result->getItems();
        $this->assertTrue(sizeof($items) > 0);
        $item = $items[0];
        $this->assertEquals('UNCLAIMED', $item->getTransactionStatus());
        return $result;
    }

    /**
     * @depends testCreate
     * @param $payoutBatch PayoutBatch
     * @return PayoutBatch
     */
    public function testGet($payoutBatch)
    {
        $result = Payout::get($payoutBatch->getBatchHeader()->getPayoutBatchId(), $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        $this->assertNotNull($result->getBatchHeader()->getBatchStatus());
        $this->assertEquals(PayoutsFunctionalTest::$batchId, $result->getBatchHeader()->getSenderBatchHeader()->getSenderBatchId());
        return $result;
    }

    /**
     * @depends testCreate
     * @param $payoutBatch PayoutBatch
     * @return PayoutBatch
     */
    public function testGetItem($payoutBatch)
    {
        $items = $payoutBatch->getItems();
        $item = $items[0];
        $result = PayoutItem::get($item->getPayoutItemId(), $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        $this->assertEquals($item->getPayoutItemId(), $result->getPayoutItemId());
        $this->assertEquals($item->getPayoutBatchId(), $result->getPayoutBatchId());
        $this->assertEquals($item->getTransactionId(), $result->getTransactionId());
        $this->assertEquals($item->getPayoutItemFee(), $result->getPayoutItemFee());
    }

    /**
     * @depends testCreate
     * @param $payoutBatch PayoutBatch
     * @return PayoutBatch
     */
    public function testCancel($payoutBatch)
    {
        $items = $payoutBatch->getItems();
        $item = $items[0];
        if ($item->getTransactionStatus() != 'UNCLAIMED') {
            $this->markTestSkipped('Transaction status needs to be Unclaimed for this test ');
            return;
        }
        $result = PayoutItem::cancel($item->getPayoutItemId(), $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        $this->assertEquals($item->getPayoutItemId(), $result->getPayoutItemId());
        $this->assertEquals($item->getPayoutBatchId(), $result->getPayoutBatchId());
        $this->assertEquals($item->getTransactionId(), $result->getTransactionId());
        $this->assertEquals($item->getPayoutItemFee(), $result->getPayoutItemFee());
        $this->assertEquals('RETURNED', $result->getTransactionStatus());
    }

}
