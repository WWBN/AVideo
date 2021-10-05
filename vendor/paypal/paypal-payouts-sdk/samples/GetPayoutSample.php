<?php

namespace Sample;

require __DIR__ . '/../vendor/autoload.php';

use Sample\PayPalClient;
use PaypalPayoutsSDK\Payouts\PayoutsGetRequest;
use Sample\CreatePayoutSample;
use PayPalHttp\HttpException;

class GetPayoutSample
{

    /**
     * This function can be used to create payout. 
     */
    public static function GetPayout($batchId, $debug = false)
    {
        try {
            $request = new PayoutsGetRequest($batchId);
            $client = PayPalClient::client();
            $response = $client->execute($request);
            if ($debug) {
                print "Status Code: {$response->statusCode}\n";
                print "Status: {$response->result->batch_header->batch_status}\n";
                print "Batch ID: {$response->result->batch_header->payout_batch_id}\n";
                print "First Item ID: {$response->result->items[0]->payout_item_id}\n";

                print "Links:\n";
                foreach ($response->result->links as $link) {
                    print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
                }
                // To toggle printing the whole response body comment/uncomment below line
                echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
            }
            return $response;
        } catch (HttpException $e) {
            //Parse failure response
            echo $e->getMessage() . "\n";
            $error = json_decode($e->getMessage());
            echo $error->message . "\n";
            echo $error->name . "\n";
            echo $error->debug_id . "\n";
        }
    }
}

if (!count(debug_backtrace())) {
    $response =  CreatePayoutSample::CreatePayout(true);
    GetPayoutSample::GetPayout($response->result->batch_header->payout_batch_id, true);
}
