<?php



namespace Test\Orders;

use PHPUnit\Framework\TestCase;

use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use Test\TestHarness;


class OrdersCreateTest extends TestCase
{
    private static function buildRequestBody()
    {
        return [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => "test_ref_id1",
                "amount" => [
                    "value" => "100.00",
                    "currency_code" => "USD"
                ]
            ]],
            "redirect_urls" => [
                "cancel_url" => "https://example.com/cancel",
                "return_url" => "https://example.com/return"
            ]
        ];
    }

    public static function create($client) {
        $request = new OrdersCreateRequest();
        $request->prefer("return=representation");
        $request->body = self::buildRequestBody();
        return $client->execute($request);
    }

    public function testOrdersCreateRequest()
    {
        $client = TestHarness::client();
        $response = self::create($client);
        $this->assertEquals(201, $response->statusCode);
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
