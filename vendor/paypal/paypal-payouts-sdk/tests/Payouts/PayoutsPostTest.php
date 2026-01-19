<?php



namespace Test\PaypalPayoutsSDK\Payouts;

use PHPUnit\Framework\TestCase;

use PaypalPayoutsSDK\Payouts\PayoutsPostRequest;
use Test\TestHarness;


class PayoutsPostTest extends TestCase
{
    private static function buildRequestBody()
    {
        return json_decode(
            '{
                "sender_batch_header":
                {
                  "email_subject": "SDK payouts test txn"
                },
                "items": [
                {
                  "recipient_type": "EMAIL",
                  "receiver": "payouts2342@paypal.com",
                  "note": "Your 1$ payout",
                  "sender_item_id": "Test_txn_12",
                  "amount":
                  {
                    "currency": "USD",
                    "value": "1.00"
                  }
                }]
              }',             
            true);
    }


    public static function create($client) {
      $request = new PayoutsPostRequest();
      $request->payPalPartnerAttributionId('agSzCOx4Ab9pHxgawSO');
      $request->payPalRequestId('M6a5KDUiH6-u6E2D');
      $request->body = self::buildRequestBody();

      return $client->execute($request);
  }

    public function testPayoutsPostRequest()
    {
      
        $client = TestHarness::client();
        $response = self::create($client);
        $this->assertEquals(201, $response->statusCode);
        $this->assertNotNull($response->result);

        // Add your own checks here
    }
}
