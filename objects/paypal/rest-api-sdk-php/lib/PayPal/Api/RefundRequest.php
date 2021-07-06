<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class RefundRequest
 *
 * A refund transaction.
 *
 * @package PayPal\Api
 *
 * @property \PayPal\Api\Amount amount
 * @property string description
 * @property string refund_source
 * @property string reason
 * @property string invoice_number
 * @property bool refund_advice
 */
class RefundRequest extends PayPalModel
{
    /**
     * Details including both refunded amount (to payer) and refunded fee (to payee).
     *
     * @param \PayPal\Api\Amount $amount
     * 
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Details including both refunded amount (to payer) and refunded fee (to payee).
     *
     * @return \PayPal\Api\Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Description of what is being refunded for. Character length and limitations: 255 single-byte alphanumeric characters.
     *
     * @param string $description
     * 
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Description of what is being refunded for. Character length and limitations: 255 single-byte alphanumeric characters.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Type of PayPal funding source (balance or eCheck) that can be used for auto refund.
     * Valid Values: ["INSTANT_FUNDING_SOURCE", "ECHECK", "UNRESTRICTED"]
     *
     * @param string $refund_source
     * 
     * @return $this
     */
    public function setRefundSource($refund_source)
    {
        $this->refund_source = $refund_source;
        return $this;
    }

    /**
     * Type of PayPal funding source (balance or eCheck) that can be used for auto refund.
     *
     * @return string
     */
    public function getRefundSource()
    {
        return $this->refund_source;
    }

    /**
     * Reason description for the Sale transaction being refunded.
     *
     * @param string $reason
     * 
     * @return $this
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * Reason description for the Sale transaction being refunded.
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * The invoice number that is used to track this payment. Character length and limitations: 127 single-byte alphanumeric characters.
     *
     * @param string $invoice_number
     * 
     * @return $this
     */
    public function setInvoiceNumber($invoice_number)
    {
        $this->invoice_number = $invoice_number;
        return $this;
    }

    /**
     * The invoice number that is used to track this payment. Character length and limitations: 127 single-byte alphanumeric characters.
     *
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->invoice_number;
    }

    /**
     * Flag to indicate that the buyer was already given store credit for a given transaction.
     *
     * @param bool $refund_advice
     * 
     * @return $this
     */
    public function setRefundAdvice($refund_advice)
    {
        $this->refund_advice = $refund_advice;
        return $this;
    }

    /**
     * Flag to indicate that the buyer was already given store credit for a given transaction.
     *
     * @return bool
     */
    public function getRefundAdvice()
    {
        return $this->refund_advice;
    }

}
