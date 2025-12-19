<?php



namespace Test\Orders;

use PHPUnit\Framework\TestCase;

use PayPalCheckoutSdk\Orders\OrdersPatchRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use Test\TestHarness;


class OrdersPatchTest extends TestCase
{
    private function buildRequestBody()
    {
        return [
            [
                "op" => "add",
                "path" => "/purchase_units/@reference_id=='test_ref_id1'/description",
                "value" => "added_description"
            ],
            [
                "op" => "replace",
                "path" => "/purchase_units/@reference_id=='test_ref_id1'/amount",
                "value" => [
                    "currency_code" => "USD",
                    "value" => "200.00"
                ]
            ]
        ];
    }

    public function testOrdersPatchRequest()
    {
        $client = TestHarness::client();
        $createdOrder = OrdersCreateTest::create($client);

        $request = new OrdersPatchRequest($createdOrder->result->id);
        $request->body = $this->buildRequestBody();
        $response = $client->execute($request);
        $this->assertEquals(204, $response->statusCode);

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
        $this->assertEquals("200.00", $firstPurchaseUnit->amount->value);
        $this->assertEquals("added_description", $firstPurchaseUnit->description);

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
