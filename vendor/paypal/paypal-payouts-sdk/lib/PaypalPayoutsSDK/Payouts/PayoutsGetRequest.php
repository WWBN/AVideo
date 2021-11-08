<?php

// This class was generated on Fri, 20 Dec 2019 15:17:28 IST by version 0.1.0-dev+904328-dirty of Braintree SDK Generator
// PayoutsGetRequest.php
// @version 0.1.0-dev+904328-dirty
// @type request
// @data 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
// DO NOT EDIT

namespace PaypalPayoutsSDK\Payouts;

use PayPalHttp\HttpRequest;

class PayoutsGetRequest extends HttpRequest
{
    function __construct($payoutBatchId)
    {
        parent::__construct("/v1/payments/payouts/{payout_batch_id}?", "GET");
        
        $this->path = str_replace("{payout_batch_id}", urlencode($payoutBatchId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }

    
    public function fields($fields)
    {
        $param = $fields;
        $this->path .= "fields=". urlencode($param) . "&";
    }
    public function page($page)
    {
        $param = $page;
        $this->path .= "page=". urlencode($param) . "&";
    }
    public function pageSize($pageSize)
    {
        $param = $pageSize;
        $this->path .= "page_size=". urlencode($param) . "&";
    }
    public function totalRequired($totalRequired)
    {
        $param = $totalRequired;
        $this->path .= "total_required=". urlencode($param) . "&";
    }
}
