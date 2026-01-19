<?php

require __DIR__ . '/../../vendor/autoload.php';

use Sample\CaptureIntentExamples\CreateOrder;
use Sample\CaptureIntentExamples\CaptureOrder;
use Sample\RefundOrder;

$order = CreateOrder::createOrder();

print "Creating Order...\n";
$orderId = "";
if ($order->statusCode == 201)
{
    $orderId = $order->result->id;
    print "Links:\n";
    for ($i = 0; $i < count($order->result->links); ++$i)
    {
        $link = $order->result->links[$i];
        print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
    }
    print "Created Successfully\n";
    print "Copy approve link and paste it in browser. Login with buyer account and follow the instructions.\nOnce approved hit enter...\n";
}
else {
    exit(1);
}

$handle = fopen ("php://stdin","r");
$line = fgets($handle);
fclose($handle);

print "Capturing Order...\n";
$response = CaptureOrder::captureOrder($orderId);
if ($response->statusCode == 201)
{
    print "Captured Successfully\n";
    print "Status Code: {$response->statusCode}\n";
    print "Status: {$response->result->status}\n";
    print "Order ID: {$response->result->id}\n";
    print "Links:\n";
    for ($i = 0; $i < count($response->result->links); ++$i){
        $link = $response->result->links[$i];
        print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
    }
    foreach($response->result->purchase_units as $purchase_unit)
    {
        foreach($purchase_unit->payments->captures as $capture)
        {    
            $captureId = $capture->id;
        }
    }
}
else {
    exit(1);
}

print "\nRefunding Order...\n";
$response = RefundOrder::refundOrder($captureId);
if ($response->statusCode == 201)
{
    print "Refunded Successfully\n";
    print "Status Code: {$response->statusCode}\n";
    print "Status: {$response->result->status}\n";
    print "Refund ID: {$response->result->id}\n";
    print "Links:\n";
    for ($i = 0; $i < count($response->result->links); ++$i){
        $link = $response->result->links[$i];
        print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
    }
}
else {
    exit(1);
}
