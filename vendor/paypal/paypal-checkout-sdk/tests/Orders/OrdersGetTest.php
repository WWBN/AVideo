<?php



namespace Test\Orders;

use PHPUnit\Framework\TestCase;

use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use Test\TestHarness;


class OrdersGetTest extends TestCase
{

    public function testOrdersGetRequest()
    {
        $client = TestHarness::client();
        $createdOrder = OrdersCreateTest::create($client);

        $request = new OrdersGetRequest($createdOrder->result->id);
        $response = $client->execute($request);
        $this->assertEquals(200, $response->statusCode);
        $this->assertNotNull($response->result);

        $createdOrder = $response->result;
        $this->assertNotNull($createdOrder->id);
        $this->assertNotNull($createdOrder->purchase_units);
        $this->assertEquals(1, count($createdOrder->purchase_units));
        $firstPurchaseUnit = $createdOrder->purchase_units[0];
        $this->assertEquals("test_ref_id1", $firstPurchaseUnit->reference_id);
        $this->assertEquals("USD", $firstPurchaseUnit->amount->currency_code);
        $this->assertEquals("100.00", $firstPurchaseUnit->amount->value);

        $this->assertNotNull($createdOrder->create_time);
        $this->assertNotNull($createdOrder->links);
        $foundApproveUrl = false;
        foreach ($createdOrder->links as $link) {
            if ("approve" === $link->rel) {
                $foundApproveUrl = true;
                $this->assertNotNull($link->href);
                $this->assertEquals("GET", $link->method);
            }
        }
        $this->assertTrue($foundApproveUrl);
        $this->assertEquals("CREATED", $createdOrder->status);
    }
}
