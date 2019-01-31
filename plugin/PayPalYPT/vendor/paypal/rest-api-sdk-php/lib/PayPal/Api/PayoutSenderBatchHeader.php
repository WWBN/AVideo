<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class PayoutSenderBatchHeader
 *
 * The sender-provided batch header for a batch payout request.
 *
 * @package PayPal\Api
 *
 * @property string sender_batch_id
 * @property string email_subject
 * @property string recipient_type
 * @property string batch_status
 */
class PayoutSenderBatchHeader extends PayPalModel
{
    /**
     * A sender-specified ID number. Tracks the batch payout in an accounting system.<blockquote><strong>Note:</strong> PayPal prevents duplicate batches from being processed. If you specify a `sender_batch_id` that was used in the last 30 days, the API rejects the request and returns an error message that indicates the duplicate `sender_batch_id` and includes a HATEOAS link to the original batch payout with the same `sender_batch_id`. If you receive a HTTP `5nn` status code, you can safely retry the request with the same `sender_batch_id`. In any case, the API completes a payment only once for a specific `sender_batch_id` that is used within 30 days.</blockquote>
     *
     * @param string $sender_batch_id
     * 
     * @return $this
     */
    public function setSenderBatchId($sender_batch_id)
    {
        $this->sender_batch_id = $sender_batch_id;
        return $this;
    }

    /**
     * A sender-specified ID number. Tracks the batch payout in an accounting system.<blockquote><strong>Note:</strong> PayPal prevents duplicate batches from being processed. If you specify a `sender_batch_id` that was used in the last 30 days, the API rejects the request and returns an error message that indicates the duplicate `sender_batch_id` and includes a HATEOAS link to the original batch payout with the same `sender_batch_id`. If you receive a HTTP `5nn` status code, you can safely retry the request with the same `sender_batch_id`. In any case, the API completes a payment only once for a specific `sender_batch_id` that is used within 30 days.</blockquote>
     *
     * @return string
     */
    public function getSenderBatchId()
    {
        return $this->sender_batch_id;
    }

    /**
     * The subject line text for the email that PayPal sends when a payout item completes. The subject line is the same for all recipients. Value is an alphanumeric string with a maximum length of 255 single-byte characters.
     *
     * @param string $email_subject
     * 
     * @return $this
     */
    public function setEmailSubject($email_subject)
    {
        $this->email_subject = $email_subject;
        return $this;
    }

    /**
     * The subject line text for the email that PayPal sends when a payout item completes. The subject line is the same for all recipients. Value is an alphanumeric string with a maximum length of 255 single-byte characters.
     *
     * @return string
     */
    public function getEmailSubject()
    {
        return $this->email_subject;
    }

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
     * @deprecated This property is unused
     */
    public function setBatchStatus($batch_status)
    {
        $this->batch_status = $batch_status;
        return $this;
    }

    /**
     * @deprecated This property is unused
     */
    public function getBatchStatus()
    {
        return $this->batch_status;
    }

}
