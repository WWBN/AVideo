<?php

namespace Sample\CaptureIntentExamples;

require __DIR__ . '/../../vendor/autoload.php';


use Sample\PayPalClient;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

class CreateOrder
{
    
    /**
     * Setting up the JSON request body for creating the Order. The Intent in the
     * request body should be set as "CAPTURE" for capture intent flow.
     * 
     */
    private static function buildRequestBody()
    {
        return array(
            'intent' => 'CAPTURE',
            'application_context' =>
                array(
                    'return_url' => 'https://example.com/return',
                    'cancel_url' => 'https://example.com/cancel',
                    'brand_name' => 'EXAMPLE INC',
                    'locale' => 'en-US',
                    'landing_page' => 'BILLING',
                    'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                    'user_action' => 'PAY_NOW',
                ),
            'purchase_units' =>
                array(
                    0 =>
                        array(
                            'reference_id' => 'PUHF',
                            'description' => 'Sporting Goods',
                            'custom_id' => 'CUST-HighFashions',
                            'soft_descriptor' => 'HighFashions',
                            'amount' =>
                                array(
                                    'currency_code' => 'USD',
                                    'value' => '220.00',
                                    'breakdown' =>
                                        array(
                                            'item_total' =>
                                                array(
                                                    'currency_code' => 'USD',
                                                    'value' => '180.00',
                                                ),
                                            'shipping' =>
                                                array(
                                                    'currency_code' => 'USD',
                                                    'value' => '20.00',
                                                ),
                                            'handling' =>
                                                array(
                                                    'currency_code' => 'USD',
                                                    'value' => '10.00',
                                                ),
                                            'tax_total' =>
                                                array(
                                                    'currency_code' => 'USD',
                                                    'value' => '20.00',
                                                ),
                                            'shipping_discount' =>
                                                array(
                                                    'currency_code' => 'USD',
                                                    'value' => '10.00',
                                                ),
                                        ),
                                ),
                            'items' =>
                                array(
                                    0 =>
                                        array(
                                            'name' => 'T-Shirt',
                                            'description' => 'Green XL',
                                            'sku' => 'sku01',
                                            'unit_amount' =>
                                                array(
                                                    'currency_code' => 'USD',
                                                    'value' => '90.00',
                                                ),
                                            'tax' =>
                                                array(
                                                    'currency_code' => 'USD',
                                                    'value' => '10.00',
                                                ),
                                            'quantity' => '1',
                                            'category' => 'PHYSICAL_GOODS',
                                        ),
                                    1 =>
                                        array(
                                            'name' => 'Shoes',
                                            'description' => 'Running, Size 10.5',
                                            'sku' => 'sku02',
                                            'unit_amount' =>
                                                array(
                                                    'currency_code' => 'USD',
                                                    'value' => '45.00',
                                                ),
                                            'tax' =>
                                                array(
                                                    'currency_code' => 'USD',
                                                    'value' => '5.00',
                                                ),
                                            'quantity' => '2',
                                            'category' => 'PHYSICAL_GOODS',
                                        ),
                                ),
                            'shipping' =>
                                array(
                                    'method' => 'United States Postal Service',
                                    'name' =>
                                        array(
                                            'full_name' => 'John Doe',
                                        ),
                                    'address' =>
                                        array(
                                            'address_line_1' => '123 Townsend St',
                                            'address_line_2' => 'Floor 6',
                                            'admin_area_2' => 'San Francisco',
                                            'admin_area_1' => 'CA',
                                            'postal_code' => '94107',
                                            'country_code' => 'US',
                                        ),
                                ),
                        ),
                ),
        );
    }

    /**
     * This is the sample function which can be sued to create an order. It uses the
     * JSON body returned by buildRequestBody() to create an new Order.
     */
    public static function createOrder($debug=false)
    {
        $request = new OrdersCreateRequest();
        $request->headers["prefer"] = "return=representation";
        $request->body = self::buildRequestBody();

        $client = PayPalClient::client();
        $response = $client->execute($request);
        if ($debug)
        {
            print "Status Code: {$response->statusCode}\n";
            print "Status: {$response->result->status}\n";
            print "Order ID: {$response->result->id}\n";
            print "Intent: {$response->result->intent}\n";
            print "Links:\n";
            foreach($response->result->links as $link)
            {
                print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
            }
            // To toggle printing the whole response body comment/uncomment below line
            echo json_encode($response->result, JSON_PRETTY_PRINT), "\n";
        }


        return $response;
    }
}


/**
 * This is the driver function which invokes the createOrder function to create
 * an sample order.
 */
if (!count(debug_backtrace()))
{
    CreateOrder::createOrder(true);
}



