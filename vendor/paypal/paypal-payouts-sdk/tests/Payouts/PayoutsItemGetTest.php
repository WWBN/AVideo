<?php



namespace Test\PaypalPayoutsSDK\Payouts;

use PHPUnit\Framework\TestCase;

use PaypalPayoutsSDK\Payouts\PayoutsItemGetRequest;
use Test\TestHarness;


class PayoutsItemGetTest extends TestCase
{

    public function testPayoutsItemGetRequest()
    {
        $request = new PayoutsItemGetRequest('SU7ZP34G56PRG');

        $client = TestHarness::client();
        $response = $client->execute($request);
        $this->assertEquals(200, $response->statusCode);
        $this->assertNotNull($response->result);

        // Add your own checks here
    }
}
