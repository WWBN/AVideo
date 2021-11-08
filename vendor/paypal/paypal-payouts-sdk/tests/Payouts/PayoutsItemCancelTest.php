<?php



namespace Test\PaypalPayoutsSDK\Payouts;

use PHPUnit\Framework\TestCase;

use PaypalPayoutsSDK\Payouts\PayoutsItemCancelRequest;
use Test\TestHarness;


class PayoutsItemCancelTest extends TestCase
{

    public function testPayoutsItemCancelRequest()
    {
        $client = TestHarness::client();

        $payout = PayoutsGetTest::get($client);
        
        sleep(10);

        $request = new PayoutsItemCancelRequest($payout->result->items[0]->payout_item_id);

        $client = TestHarness::client();
        $response = $client->execute($request);
        $this->assertEquals(200, $response->statusCode);
        $this->assertNotNull($response->result);

        // Add your own checks here
    }
}
