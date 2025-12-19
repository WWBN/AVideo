<?php

namespace Sample;

require __DIR__ . '/../vendor/autoload.php';

use Sample\PayPalClient;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use Sample\CaptureIntentExamples\CreateOrder;

class GetOrder
{

    /**
     * This function can be used to retrieve an order by passing order Id as argument.
     */
    public static function getOrder($orderId)
    {
        
        $client = PayPalClient::client();
        $response = $client->execute(new OrdersGetRequest($orderId));
        /**
         * Enable below line to print complete response as JSON.
         */
        //print json_encode($response->result);
        print "Status Code: {$response->statusCode}\n";
        print "Status: {$response->result->status}\n";
        print "Order ID: {$response->result->id}\n";
        print "Intent: {$response->result->intent}\n";
        print "Links:\n";
        foreach($response->result->links as $link)
        {
            print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
        }

        print "Gross Amount: {$response->result->purchase_units[0]->amount->currency_code} {$response->result->purchase_units[0]->amount->value}\n";

        // To toggle printing the whole response body comment/uncomment below line
        echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
    }
}

/**
 * This is the driver function which invokes the getOrder function to retrieve
 * an sample order.
 * 
 * To get the correct Order id, we are using the createOrder to create new order
 * and then we are using the newly created order id.
 */
if (!count(debug_backtrace()))
{
    $createdOrder = CreateOrder::createOrder()->result;
    GetOrder::getOrder($createdOrder ->id);
}