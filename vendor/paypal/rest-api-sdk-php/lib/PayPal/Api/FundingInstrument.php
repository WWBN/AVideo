<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class FundingInstrument
 *
 * A resource representing a Payer's funding instrument. An instance of this schema is valid if and only if it is valid against exactly one of these supported properties
 *
 * @package PayPal\Api
 *
 * @property \PayPal\Api\CreditCard      credit_card
 * @property \PayPal\Api\CreditCardToken credit_card_token
 * @property \PayPal\Api\Billing         billing
 */
class FundingInstrument extends PayPalModel
{
    /**
     * Credit Card instrument.
     *
     * @param \PayPal\Api\CreditCard $credit_card
     *
     * @return $this
     */
    public function setCreditCard($credit_card)
    {
        $this->credit_card = $credit_card;
        return $this;
    }

    /**
     * Credit Card instrument.
     *
     * @return \PayPal\Api\CreditCard
     */
    public function getCreditCard()
    {
        return $this->credit_card;
    }

    /**
     * PayPal vaulted credit Card instrument.
     *
     * @param \PayPal\Api\CreditCardToken $credit_card_token
     *
     * @return $this
     */
    public function setCreditCardToken($credit_card_token)
    {
        $this->credit_card_token = $credit_card_token;
        return $this;
    }

    /**
     * PayPal vaulted credit Card instrument.
     *
     * @return \PayPal\Api\CreditCardToken
     */
    public function getCreditCardToken()
    {
        return $this->credit_card_token;
    }

    /**
     * Payment Card information.
     *
     * @deprecated Not publicly available
     * @param \PayPal\Api\PaymentCard $payment_card
     *
     * @return $this
     */
    public function setPaymentCard($payment_card)
    {
        $this->payment_card = $payment_card;
        return $this;
    }

    /**
     * Payment Card information.
     *
     * @deprecated Not publicly available
     * @return \PayPal\Api\PaymentCard
     */
    public function getPaymentCard()
    {
        return $this->payment_card;
    }

    /**
     * Bank Account information.
     *
     * @deprecated Not publicly available
     * @param \PayPal\Api\ExtendedBankAccount $bank_account
     *
     * @return $this
     */
    public function setBankAccount($bank_account)
    {
        $this->bank_account = $bank_account;
        return $this;
    }

    /**
     * Bank Account information.
     *
     * @deprecated Not publicly available
     * @return \PayPal\Api\ExtendedBankAccount
     */
    public function getBankAccount()
    {
        return $this->bank_account;
    }

    /**
     * Vaulted bank account instrument.
     *
     * @deprecated Not publicly available
     * @param \PayPal\Api\BankToken $bank_account_token
     *
     * @return $this
     */
    public function setBankAccountToken($bank_account_token)
    {
        $this->bank_account_token = $bank_account_token;
        return $this;
    }

    /**
     * Vaulted bank account instrument.
     *
     * @deprecated Not publicly available
     * @return \PayPal\Api\BankToken
     */
    public function getBankAccountToken()
    {
        return $this->bank_account_token;
    }

    /**
     * PayPal credit funding instrument.
     *
     * @deprecated Not publicly available
     * @param \PayPal\Api\Credit $credit
     *
     * @return $this
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;
        return $this;
    }

    /**
     * PayPal credit funding instrument.
     *
     * @deprecated Not publicly available
     * @return \PayPal\Api\Credit
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * Incentive funding instrument.
     *
     * @deprecated Not publicly available
     * @param \PayPal\Api\Incentive $incentive
     *
     * @return $this
     */
    public function setIncentive($incentive)
    {
        $this->incentive = $incentive;
        return $this;
    }

    /**
     * Incentive funding instrument.
     *
     * @deprecated Not publicly available
     * @return \PayPal\Api\Incentive
     */
    public function getIncentive()
    {
        return $this->incentive;
    }

    /**
     * External funding instrument.
     *
     * @deprecated Not publicly available
     * @param \PayPal\Api\ExternalFunding $external_funding
     *
     * @return $this
     */
    public function setExternalFunding($external_funding)
    {
        $this->external_funding = $external_funding;
        return $this;
    }

    /**
     * External funding instrument.
     *
     * @deprecated Not publicly available
     * @return \PayPal\Api\ExternalFunding
     */
    public function getExternalFunding()
    {
        return $this->external_funding;
    }

    /**
     * Carrier account token instrument.
     *
     * @deprecated Not publicly available
     * @param \PayPal\Api\CarrierAccountToken $carrier_account_token
     *
     * @return $this
     */
    public function setCarrierAccountToken($carrier_account_token)
    {
        $this->carrier_account_token = $carrier_account_token;
        return $this;
    }

    /**
     * Carrier account token instrument.
     *
     * @deprecated Not publicly available
     * @return \PayPal\Api\CarrierAccountToken
     */
    public function getCarrierAccountToken()
    {
        return $this->carrier_account_token;
    }

    /**
     * Carrier account instrument
     *
     * @deprecated Not publicly available
     * @param \PayPal\Api\CarrierAccount $carrier_account
     *
     * @return $this
     */
    public function setCarrierAccount($carrier_account)
    {
        $this->carrier_account = $carrier_account;
        return $this;
    }

    /**
     * Carrier account instrument
     *
     * @deprecated Not publicly available
     * @return \PayPal\Api\CarrierAccount
     */
    public function getCarrierAccount()
    {
        return $this->carrier_account;
    }

    /**
     * Private Label Card funding instrument. These are store cards provided by merchants to drive business with value to customer with convenience and rewards.
     *
     * @deprecated Not publicly available
     * @param \PayPal\Api\PrivateLabelCard $private_label_card
     *
     * @return $this
     */
    public function setPrivateLabelCard($private_label_card)
    {
        $this->private_label_card = $private_label_card;
        return $this;
    }

    /**
     * Private Label Card funding instrument. These are store cards provided by merchants to drive business with value to customer with convenience and rewards.
     *
     * @deprecated Not publicly available
     * @return \PayPal\Api\PrivateLabelCard
     */
    public function getPrivateLabelCard()
    {
        return $this->private_label_card;
    }

    /**
     * Billing instrument that references pre-approval information for the payment
     *
     * @param \PayPal\Api\Billing $billing
     *
     * @return $this
     */
    public function setBilling($billing)
    {
        $this->billing = $billing;
        return $this;
    }

    /**
     * Billing instrument that references pre-approval information for the payment
     *
     * @return \PayPal\Api\Billing
     */
    public function getBilling()
    {
        return $this->billing;
    }

    /**
     * Alternate Payment  information - Mostly regional payment providers. For e.g iDEAL in Netherlands
     *
     * @deprecated Not publicly available
     * @param \PayPal\Api\AlternatePayment $alternate_payment
     *
     * @return $this
     */
    public function setAlternatePayment($alternate_payment)
    {
        $this->alternate_payment = $alternate_payment;
        return $this;
    }

    /**
     * Alternate Payment  information - Mostly regional payment providers. For e.g iDEAL in Netherlands
     *
     * @deprecated Not publicly available
     * @return \PayPal\Api\AlternatePayment
     */
    public function getAlternatePayment()
    {
        return $this->alternate_payment;
    }

}
