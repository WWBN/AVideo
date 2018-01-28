<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class PaymentDetail
 *
 * Invoicing payment information.
 *
 * @package PayPal\Api
 *
 * @property string type
 * @property string transaction_id
 * @property string transaction_type
 * @property string date
 * @property string method
 * @property string note
 * @property \PayPal\Api\Currency amount
 */
class PaymentDetail extends PayPalModel
{
    /**
     * The PayPal payment detail. Indicates whether payment was made in an invoicing flow through PayPal or externally. In the case of the mark-as-paid API, the supported payment type is `EXTERNAL`. For backward compatibility, the `PAYPAL` payment type is still supported.
     * Valid Values: ["PAYPAL", "EXTERNAL"]
     *
     * @param string $type
     * 
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * The PayPal payment detail. Indicates whether payment was made in an invoicing flow through PayPal or externally. In the case of the mark-as-paid API, the supported payment type is `EXTERNAL`. For backward compatibility, the `PAYPAL` payment type is still supported.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The PayPal payment transaction ID. Required with the `PAYPAL` payment type.
     *
     * @param string $transaction_id
     * 
     * @return $this
     */
    public function setTransactionId($transaction_id)
    {
        $this->transaction_id = $transaction_id;
        return $this;
    }

    /**
     * The PayPal payment transaction ID. Required with the `PAYPAL` payment type.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * Type of the transaction.
     * Valid Values: ["SALE", "AUTHORIZATION", "CAPTURE"]
     *
     * @param string $transaction_type
     * 
     * @return $this
     */
    public function setTransactionType($transaction_type)
    {
        $this->transaction_type = $transaction_type;
        return $this;
    }

    /**
     * Type of the transaction.
     *
     * @return string
     */
    public function getTransactionType()
    {
        return $this->transaction_type;
    }

    /**
     * The date when the invoice was paid. The date format is *yyyy*-*MM*-*dd* *z* as defined in [Internet Date/Time Format](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @param string $date
     * 
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * The date when the invoice was paid. The date format is *yyyy*-*MM*-*dd* *z* as defined in [Internet Date/Time Format](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * The payment mode or method. Required with the `EXTERNAL` payment type.
     * Valid Values: ["BANK_TRANSFER", "CASH", "CHECK", "CREDIT_CARD", "DEBIT_CARD", "PAYPAL", "WIRE_TRANSFER", "OTHER"]
     *
     * @param string $method
     * 
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * The payment mode or method. Required with the `EXTERNAL` payment type.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Optional. A note associated with the payment.
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
     * Optional. A note associated with the payment.
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * The amount to record as payment against invoice. If you omit this parameter, the total invoice amount is recorded as payment.
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
     * The amount to record as payment against invoice. If you omit this parameter, the total invoice amount is recorded as payment.
     *
     * @return \PayPal\Api\Currency
     */
    public function getAmount()
    {
        return $this->amount;
    }

}
