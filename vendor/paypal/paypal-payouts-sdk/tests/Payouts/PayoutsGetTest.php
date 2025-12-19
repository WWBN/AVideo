<?php



namespace Test\PaypalPayoutsSDK\Payouts;

use PHPUnit\Framework\TestCase;

use PaypalPayoutsSDK\Payouts\PayoutsGetRequest;
use Test\TestHarness;


class PayoutsGetTest extends TestCase
{

    public static function get($client){
        $payout = PayoutsPostTest::create($client);

        $request = new PayoutsGetRequest($payout->result->batch_header->payout_batch_id);
        $response = $client->execute($request);
        return $response;

    }
    public function testPayoutsGetRequest()
    {
        $client = TestHarness::client();
        $response=self::get($client);
        $this->assertEquals(200, $response->statusCode);
        $this->assertNotNull($response->result);

        // Add your own checks here
    }
}
