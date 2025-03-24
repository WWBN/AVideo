<?php

namespace PayPal\Api;

/**
 * Class TransactionBase
 *
 * A transaction defines the contract of a payment - what is the payment for and who is fulfilling it.
 *
 * @package PayPal\Api
 *
 * @property \PayPal\Api\RelatedResources related_resources
 */
class TransactionBase extends CartBase 
{
    /**
     * List of financial transactions (Sale, Authorization, Capture, Refund) related to the payment.
     * 
     *
     * @param \PayPal\Api\RelatedResources $related_resources
     * 
     * @return $this
     */
    public function setRelatedResources($related_resources)
    {
        $this->related_resources = $related_resources;
        return $this;
    }

    /**
     * List of financial transactions (Sale, Authorization, Capture, Refund) related to the payment.
     *
     * @return \PayPal\Api\RelatedResources[]
     */
    public function getRelatedResources()
    {
        return $this->related_resources;
    }

}
