<?php

namespace PayPal\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Transport\PayPalRestCall;
use PayPal\Validation\ArgumentValidator;
use PayPal\Rest\ApiContext;

/**
 * Class Capture
 *
 * A capture transaction.
 *
 * @package PayPal\Api
 *
 * @property string id
 * @property \PayPal\Api\Amount amount
 * @property bool is_final_capture
 * @property string state
 * @property string reason_code
 * @property string parent_payment
 * @property string invoice_number
 * @property \PayPal\Api\Currency transaction_fee
 * @property string create_time
 * @property string update_time
 * @property \PayPal\Api\Links[] links
 */
class Capture extends PayPalResourceModel
{
    /**
     * The ID of the capture transaction.
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
     * The ID of the capture transaction.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * The amount to capture. If the amount matches the orginally authorized amount, the state of the authorization changes to `captured`. If not, the state of the authorization changes to `partially_captured`.
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
     * The amount to capture. If the amount matches the orginally authorized amount, the state of the authorization changes to `captured`. If not, the state of the authorization changes to `partially_captured`.
     *
     * @return \PayPal\Api\Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Indicates whether to release all remaining funds that the authorization holds in the funding instrument. Default is `false`.
     *
     * @param bool $is_final_capture
     * 
     * @return $this
     */
    public function setIsFinalCapture($is_final_capture)
    {
        $this->is_final_capture = $is_final_capture;
        return $this;
    }

    /**
     * Indicates whether to release all remaining funds that the authorization holds in the funding instrument. Default is `false`.
     *
     * @return bool
     */
    public function getIsFinalCapture()
    {
        return $this->is_final_capture;
    }

    /**
     * The state of the capture.
     * Valid Values: ["pending", "completed", "refunded", "partially_refunded"]
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
     * The state of the capture.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * The reason code that describes why the transaction state is pending or reversed.
     * Valid Values: ["CHARGEBACK", "GUARANTEE", "BUYER_COMPLAINT", "REFUND", "UNCONFIRMED_SHIPPING_ADDRESS", "ECHECK", "INTERNATIONAL_WITHDRAWAL", "RECEIVING_PREFERENCE_MANDATES_MANUAL_ACTION", "PAYMENT_REVIEW", "REGULATORY_REVIEW", "UNILATERAL", "VERIFICATION_REQUIRED", "TRANSACTION_APPROVED_AWAITING_FUNDING"]
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
     * The reason code that describes why the transaction state is pending or reversed.
     *
     * @return string
     */
    public function getReasonCode()
    {
        return $this->reason_code;
    }

    /**
     * The ID of the payment on which this transaction is based.
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
     * The ID of the payment on which this transaction is based.
     *
     * @return string
     */
    public function getParentPayment()
    {
        return $this->parent_payment;
    }

    /**
     * The invoice number to track this payment.
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
     * The invoice number to track this payment.
     *
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->invoice_number;
    }

    /**
     * The transaction fee for this payment.
     *
     * @param \PayPal\Api\Currency $transaction_fee
     * 
     * @return $this
     */
    public function setTransactionFee($transaction_fee)
    {
        $this->transaction_fee = $transaction_fee;
        return $this;
    }

    /**
     * The transaction fee for this payment.
     *
     * @return \PayPal\Api\Currency
     */
    public function getTransactionFee()
    {
        return $this->transaction_fee;
    }

    /**
     * The date and time of capture, as defined in [RFC 3339 Section 5.6](http://tools.ietf.org/html/rfc3339#section-5.6).
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
     * The date and time of capture, as defined in [RFC 3339 Section 5.6](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * The date and time when the resource was last updated.
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
     * The date and time when the resource was last updated.
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->update_time;
    }

    /**
     * Shows details for a captured payment, by ID.
     *
     * @param string $captureId
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Capture
     */
    public static function get($captureId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($captureId, 'captureId');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/payments/capture/$captureId",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new Capture();
        $ret->fromJson($json);
        return $ret;
    }

    /**
     * Refund a captured payment by passing the capture_id in the request URI. In addition, include an amount object in the body of the request JSON.
     *
     * @deprecated Please use #refundCapturedPayment instead.
     * @param Refund         $refund
     * @param ApiContext     $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall   is the Rest Call Service that is used to make rest calls
     * @return Refund
     */
    public function refund($refund, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($this->getId(), "Id");
        ArgumentValidator::validate($refund, 'refund');
        $payLoad = $refund->toJSON();
        $json = self::executeCall(
            "/v1/payments/capture/{$this->getId()}/refund",
            "POST",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new Refund();
        $ret->fromJson($json);
        return $ret;
    }

    /**
     * Refunds a captured payment, by ID. Include an `amount` object in the JSON request body.
     *
     * @param RefundRequest $refundRequest
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return DetailedRefund
     */
    public function refundCapturedPayment($refundRequest, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($this->getId(), "Id");
        ArgumentValidator::validate($refundRequest, 'refundRequest');
        $payLoad = $refundRequest->toJSON();
        $json = self::executeCall(
            "/v1/payments/capture/{$this->getId()}/refund",
            "POST",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new DetailedRefund();
        $ret->fromJson($json);
        return $ret;
    }

}
