<?php

require __DIR__ . '/../vendor/autoload.php';

use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use Sample\PayPalClient;
use PayPalHttp\HttpException;

class ErrorSample
{
    public static function prettyPrint($jsonData, $pre="")
    {
        $pretty = "";
        foreach ($jsonData as $key => $val)
        {
            $pretty .= $pre . ucfirst($key) .": ";
            if (strcmp(gettype($val), "array") == 0){
                $pretty .= "\n";
                $sno = 1;
                foreach ($val as $value)
                {
                    $pretty .= $pre . "\t" . $sno++ . ":\n";
                    $pretty .= self::prettyPrint($value, $pre . "\t\t");
                }
            }
            else {
                $pretty .= $val . "\n";
            }
        }
        return $pretty;
    }

    /**
     * Body has no required parameters (intent, purchase_units)
     */
    public static function createError1()
    {
        $request = new OrdersCreateRequest();
        $request->body = "{}";
        print "Request Body: {}\n\n";

        print "Response:\n";
        try{
            $client = PayPalClient::client();
            $response = $client->execute($request);
        }
        catch(HttpException $exception){
            $message = json_decode($exception->getMessage(), true);
            print "Status Code: {$exception->statusCode}\n";
            print(self::prettyPrint($message));
        }
    }

    /**
     * Body has invalid parameter value for intent
     */
    public static function createError2()
    {
        $request = new OrdersCreateRequest();
        $request->body = array (
            'intent' => 'INVALID',
            'purchase_units' =>
                array (
                    0 =>
                        array (
                            'amount' =>
                                array (
                                    'currency_code' => 'USD',
                                    'value' => '100.00',
                                ),
                        ),
                ),
        );
        print "Request Body:\n" . json_encode($request->body, JSON_PRETTY_PRINT) . "\n\n";

        try{
            $client = PayPalClient::client();
            $response = $client->execute($request);
        }
        catch(HttpException $exception){
            print "Response:\n";
            $message = json_decode($exception->getMessage(), true);
            print "Status Code: {$exception->statusCode}\n";
            print(self::prettyPrint($message));
        }

    }
}

print "Calling createError1 (Body has no required parameters (intent, purchase_units))\n";
ErrorSample::createError1();

print "\n\nCalling createError2 (Body has invalid parameter value for intent)\n";
ErrorSample::createError2();
