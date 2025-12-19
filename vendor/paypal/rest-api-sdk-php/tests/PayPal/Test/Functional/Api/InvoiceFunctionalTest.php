<?php

namespace PayPal\Test\Functional\Api;
use PayPal\Api\CancelNotification;
use PayPal\Api\Invoice;
use PayPal\Api\Notification;
use PayPal\Api\PaymentDetail;
use PayPal\Api\RefundDetail;
use PayPal\Api\Search;
use PayPal\Test\Functional\Setup;

/**
 * Class Invoice
 *
 * @package PayPal\Test\Api
 */
class InvoiceFunctionalTest extends \PHPUnit_Framework_TestCase
{

    public static $obj;

    public $operation;

    public $response;

    public $mockPayPalRestCall;

    public $apiContext;

    public function setUp()
    {
        $className = $this->getClassName();
        $testName = $this->getName();
        $this->setupTest($className, $testName);
    }

    public function setupTest($className, $testName)
    {
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
        $obj = new Invoice($request);
        $result = $obj->create($this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        self::$obj = $result;
        return $result;
    }

    /**
     * @depends testCreate
     * @param $invoice Invoice
     * @return Invoice
     */
    public function testGet($invoice)
    {
        $result = Invoice::get($invoice->getId(), $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        $this->assertEquals($invoice->getId(), $result->getId());
        return $result;
    }

    /**
     * @depends testCreate
     * @param $invoice Invoice
     * @return Invoice
     */
    public function testSend($invoice)
    {
        $result = $invoice->send($this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        return $invoice;
    }

    /**
     * @depends testSend
     * @param $invoice Invoice
     * @return Invoice
     */
    public function testGetAll($invoice)
    {
        $result = Invoice::getAll(array('page_size' => '20', 'total_count_required' => 'true'), $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        $this->assertNotNull($result->getTotalCount());
        $totalPages = ceil($result->getTotalCount()/20);
        $found = false;
        $foundObject = null;
        do {
            foreach ($result->getInvoices() as $obj) {
                if ($obj->getId() == $invoice->getId()) {
                    $found = true;
                    $foundObject = $obj;
                    break;
                }
            }
            if (!$found) {
                $result = Invoice::getAll(array('page' => --$totalPages, 'page_size' => '20', 'total_required' => 'yes'), $this->apiContext, $this->mockPayPalRestCall);

            }
        } while ($totalPages > 0 && $found == false);
        $this->assertTrue($found, "The Created Invoice was not found in the get list");
        $this->assertEquals($invoice->getId(), $foundObject->getId());
    }


    /**
     * @depends testSend
     * @param $invoice Invoice
     * @return Invoice
     */
    public function testUpdate($invoice)
    {
        $result = $invoice->update($this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        $this->assertEquals($invoice->getId(), $result->getId());
    }

    /**
     * @depends testSend
     * @param $invoice Invoice
     * @return Invoice
     */
    public function testSearch($invoice)
    {
        $request = $this->operation['request']['body'];
        $search = new Search($request);
        $result = Invoice::search($search, $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        $this->assertNotNull($result->getTotalCount());
    }

    /**
     * @depends testSend
     * @param $invoice Invoice
     * @return Invoice
     */
    public function testRemind($invoice)
    {
        $request = $this->operation['request']['body'];
        $notification = new Notification($request);
        $result = $invoice->remind($notification, $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @depends testSend
     * @param $invoice Invoice
     * @return Invoice
     */
    public function testCancel($invoice)
    {
        $request = $this->operation['request']['body'];
        $notification = new CancelNotification($request);
        $result = $invoice->cancel($notification, $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
    }

    /**
     * @depends testSend
     * @param $invoice Invoice
     * @return Invoice
     */
    public function testQRCode($invoice)
    {
        $result = Invoice::qrCode($invoice->getId(), array(), $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        $this->assertNotNull($result->getImage());
    }

    /**
     * @depends testSend
     * @param $invoice Invoice
     * @return Invoice
     */
    public function testRecordPayment($invoice)
    {
        $this->setupTest($this->getClassName(), 'testCreate');
        $invoice = $this->testCreate($invoice);
        $this->setupTest($this->getClassName(), 'testSend');
        $invoice = $this->testSend($invoice);
        $this->setupTest($this->getClassName(), 'testRecordPayment');
        $request = $this->operation['request']['body'];
        $paymentDetail = new PaymentDetail($request);
        $result = $invoice->recordPayment($paymentDetail, $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        return $invoice;
    }


    /**
     * @depends testRecordPayment
     * @param $invoice Invoice
     * @return Invoice
     */
    public function testRecordRefund($invoice)
    {
        $request = $this->operation['request']['body'];
        $refundDetail = new RefundDetail($request);
        $result = $invoice->recordRefund($refundDetail, $this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
        $this->setupTest($this->getClassName(), 'testDelete');
        $invoice = $this->testDelete($invoice);
        return $invoice;
    }

    /**
     * @depends testGet
     * @param $invoice Invoice
     * @return Invoice
     */
    public function testDelete($invoice)
    {
        $this->setupTest($this->getClassName(), 'testCreate');
        $invoice = $this->testCreate($invoice);
        $this->setupTest($this->getClassName(), 'testDelete');
        $result = $invoice->delete($this->apiContext, $this->mockPayPalRestCall);
        $this->assertNotNull($result);
    }


}
