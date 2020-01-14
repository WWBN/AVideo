<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class CarrierAccount
 *
 * Payment instrument that enables carrier billing.
 *
 * @package PayPal\Api
 *
 * @property string id
 * @property string phone_number
 * @property string external_customer_id
 * @property string phone_source
 * @property \PayPal\Api\CountryCode country_code
 */
class CarrierAccount extends PayPalModel
{
    /**
     * The ID of the carrier account of the payer. Use in subsequent REST API calls. For example, to make payments.
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
     * The ID of the carrier account of the payer. Use in subsequent REST API calls. For example, to make payments.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * The phone number of the payer, in E.164 format.
     *
     * @param string $phone_number
     * 
     * @return $this
     */
    public function setPhoneNumber($phone_number)
    {
        $this->phone_number = $phone_number;
        return $this;
    }

    /**
     * The phone number of the payer, in E.164 format.
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    /**
     * The ID of the customer, as created by the merchant.
     *
     * @param string $external_customer_id
     * 
     * @return $this
     */
    public function setExternalCustomerId($external_customer_id)
    {
        $this->external_customer_id = $external_customer_id;
        return $this;
    }

    /**
     * The ID of the customer, as created by the merchant.
     *
     * @return string
     */
    public function getExternalCustomerId()
    {
        return $this->external_customer_id;
    }

    /**
     * The method used to obtain the phone number. Value is `READ_FROM_DEVICE` or `USER_PROVIDED`.
     * Valid Values: ["READ_FROM_DEVICE", "USER_PROVIDED"]
     *
     * @param string $phone_source
     * 
     * @return $this
     */
    public function setPhoneSource($phone_source)
    {
        $this->phone_source = $phone_source;
        return $this;
    }

    /**
     * The method used to obtain the phone number. Value is `READ_FROM_DEVICE` or `USER_PROVIDED`.
     *
     * @return string
     */
    public function getPhoneSource()
    {
        return $this->phone_source;
    }

    /**
     * The ISO 3166-1 alpha-2 country code where the phone number is registered.
     *
     * @param \PayPal\Api\CountryCode $country_code
     * 
     * @return $this
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;
        return $this;
    }

    /**
     * The ISO 3166-1 alpha-2 country code where the phone number is registered.
     *
     * @return \PayPal\Api\CountryCode
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

}
