<?php

namespace PayPal\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Validation\ArgumentValidator;
use PayPal\Rest\ApiContext;

/**
 * Class Refund
 *
 * A refund transaction.
 *
 * @package PayPal\Api
 *
 * @property string id
 * @property \PayPal\Api\Amount amount
 * @property string state
 * @property string reason
 * @property string invoice_number
 * @property string sale_id
 * @property string capture_id
 * @property string parent_payment
 * @property string description
 * @property string create_time
 * @property string update_time
 * @property string reason_code
 * @property \PayPal\Api\Links[] links
 */
class Refund extends PayPalResourceModel
{
    /**
     * ID of the refund transaction. 17 characters max.
     *
     * @param string $id
     * 
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * ID of the refund transaction. 17 characters max.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Details including both refunded amount (to payer) and refunded fee (to payee). 10 characters max.
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
     * Details including both refunded amount (to payer) and refunded fee (to payee). 10 characters max.
     *
     * @return \PayPal\Api\Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * State of the refund.
     * Valid Values: ["pending", "completed", "failed"]
     *
     * @param string $state
     * 
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * State of the refund.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
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
     * Your own invoice or tracking ID number. Character length and limitations: 127 single-byte alphanumeric characters.
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
     * Your own invoice or tracking ID number. Character length and limitations: 127 single-byte alphanumeric characters.
     *
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->invoice_number;
    }

    /**
     * ID of the Sale transaction being refunded. 
     *
     * @param string $sale_id
     * 
     * @return $this
     */
    public function setSaleId($sale_id)
    {
        $this->sale_id = $sale_id;
        return $this;
    }

    /**
     * ID of the Sale transaction being refunded. 
     *
     * @return string
     */
    public function getSaleId()
    {
        return $this->sale_id;
    }

    /**
     * ID of the sale transaction being refunded.
     *
     * @param string $capture_id
     * 
     * @return $this
     */
    public function setCaptureId($capture_id)
    {
        $this->capture_id = $capture_id;
        return $this;
    }

    /**
     * ID of the sale transaction being refunded.
     *
     * @return string
     */
    public function getCaptureId()
    {
        return $this->capture_id;
    }

    /**
     * ID of the payment resource on which this transaction is based.
     *
     * @param string $parent_payment
     * 
     * @return $this
     */
    public function setParentPayment($parent_payment)
    {
        $this->parent_payment = $parent_payment;
        return $this;
    }

    /**
     * ID of the payment resource on which this transaction is based.
     *
     * @return string
     */
    public function getParentPayment()
    {
        return $this->parent_payment;
    }

    /**
     * Description of what is being refunded for.
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
     * Description of what is being refunded for.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Time of refund as defined in [RFC 3339 Section 5.6](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @param string $create_time
     * 
     * @return $this
     */
    public function setCreateTime($create_time)
    {
        $this->create_time = $create_time;
        return $this;
    }

    /**
     * Time of refund as defined in [RFC 3339 Section 5.6](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * Time that the resource was last updated.
     *
     * @param string $update_time
     * 
     * @return $this
     */
    public function setUpdateTime($update_time)
    {
        $this->update_time = $update_time;
        return $this;
    }

    /**
     * Time that the resource was last updated.
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->update_time;
    }

    /**
     * The reason code for the refund state being pending
     * Valid Values: ["ECHECK"]
     *
     * @param string $reason_code
     * 
     * @return $this
     */
    public function setReasonCode($reason_code)
    {
        $this->reason_code = $reason_code;
        return $this;
    }

    /**
     * The reason code for the refund state being pending
     *
     * @return string
     */
    public function getReasonCode()
    {
        return $this->reason_code;
    }

    /**
     * Shows details for a refund, by ID.
     *
     * @param string $refundId
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Refund
     */
    public static function get($refundId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($refundId, 'refundId');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/payments/refund/$refundId",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new Refund();
        $ret->fromJson($json);
        return $ret;
    }

}
