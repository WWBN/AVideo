<?php

namespace PayPal\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Rest\ApiContext;
use PayPal\Transport\PayPalRestCall;
use PayPal\Validation\ArgumentValidator;

/**
 * Class PayoutItem
 *
 * A sender-created definition of a payout to a single recipient.
 *
 * @package PayPal\Api
 *
 * @property string recipient_type
 * @property \PayPal\Api\Currency amount
 * @property string note
 * @property string receiver
 * @property string sender_item_id
 */
class PayoutItem extends PayPalResourceModel
{
    /**
     * The type of ID that identifies the payment receiver. Value is:<ul><code>EMAIL</code>. Unencrypted email. Value is a string of up to 127 single-byte characters.</li><li><code>PHONE</code>. Unencrypted phone number.<blockquote><strong>Note:</strong> The PayPal sandbox does not support the <code>PHONE</code> recipient type.</blockquote></li><li><code>PAYPAL_ID</code>. Encrypted PayPal account number.</li></ul>If the <code>sender_batch_header</code> includes the <code>recipient_type</code> attribute, any payout item without its own <code>recipient_type</code> attribute uses the <code>recipient_type</code> value from <code>sender_batch_header</code>. If the <code>sender_batch_header</code> omits the <code>recipient_type</code> attribute, each payout item must include its own <code>recipient_type</code> value.
     *
     * @param string $recipient_type
     * 
     * @return $this
     */
    public function setRecipientType($recipient_type)
    {
        $this->recipient_type = $recipient_type;
        return $this;
    }

    /**
     * The type of ID that identifies the payment receiver. Value is:<ul><code>EMAIL</code>. Unencrypted email. Value is a string of up to 127 single-byte characters.</li><li><code>PHONE</code>. Unencrypted phone number.<blockquote><strong>Note:</strong> The PayPal sandbox does not support the <code>PHONE</code> recipient type.</blockquote></li><li><code>PAYPAL_ID</code>. Encrypted PayPal account number.</li></ul>If the <code>sender_batch_header</code> includes the <code>recipient_type</code> attribute, any payout item without its own <code>recipient_type</code> attribute uses the <code>recipient_type</code> value from <code>sender_batch_header</code>. If the <code>sender_batch_header</code> omits the <code>recipient_type</code> attribute, each payout item must include its own <code>recipient_type</code> value.
     *
     * @return string
     */
    public function getRecipientType()
    {
        return $this->recipient_type;
    }

    /**
     * The amount of money to pay the receiver.
     *
     * @param \PayPal\Api\Currency $amount
     * 
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * The amount of money to pay the receiver.
     *
     * @return \PayPal\Api\Currency
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Optional. A sender-specified note for notifications. Value is any string value.
     *
     * @param string $note
     * 
     * @return $this
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * Optional. A sender-specified note for notifications. Value is any string value.
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * The receiver of the payment. Corresponds to the `recipient_type` value in the request.
     *
     * @param string $receiver
     * 
     * @return $this
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
        return $this;
    }

    /**
     * The receiver of the payment. Corresponds to the `recipient_type` value in the request.
     *
     * @return string
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * A sender-specified ID number. Tracks the batch payout in an accounting system.
     *
     * @param string $sender_item_id
     * 
     * @return $this
     */
    public function setSenderItemId($sender_item_id)
    {
        $this->sender_item_id = $sender_item_id;
        return $this;
    }

    /**
     * A sender-specified ID number. Tracks the batch payout in an accounting system.
     *
     * @return string
     */
    public function getSenderItemId()
    {
        return $this->sender_item_id;
    }

    /**
     * Obtain the status of a payout item by passing the item ID to the request URI.
     *
     * @param string $payoutItemId
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return PayoutItemDetails
     */
    public static function get($payoutItemId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($payoutItemId, 'payoutItemId');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/payments/payouts-item/$payoutItemId",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new PayoutItemDetails();
        $ret->fromJson($json);
        return $ret;
    }

    /**
     * Cancels the unclaimed payment using the items id passed in the request URI. If an unclaimed item is not claimed within 30 days, the funds will be automatically returned to the sender. This call can be used to cancel the unclaimed item prior to the automatic 30-day return.
     *
     * @param string $payoutItemId
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return PayoutItemDetails
     */
    public static function cancel($payoutItemId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($payoutItemId, 'payoutItemId');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/payments/payouts-item/$payoutItemId/cancel",
            "POST",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new PayoutItemDetails();
        $ret->fromJson($json);
        return $ret;
    }
}
