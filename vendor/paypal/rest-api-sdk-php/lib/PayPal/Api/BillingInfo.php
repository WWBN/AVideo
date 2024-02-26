<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class BillingInfo
 *
 * Billing information for the invoice recipient.
 *
 * @package PayPal\Api
 *
 * @property string email
 * @property string first_name
 * @property string last_name
 * @property string business_name
 * @property \PayPal\Api\InvoiceAddress address
 * @property string language
 * @property string additional_info
 * @property string notification_channel
 * @property \PayPal\Api\Phone phone
 */
class BillingInfo extends PayPalModel
{
    /**
     * The invoice recipient email address. Maximum length is 260 characters.
     *
     * @param string $email
     * 
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * The invoice recipient email address. Maximum length is 260 characters.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * The invoice recipient first name. Maximum length is 30 characters.
     *
     * @param string $first_name
     * 
     * @return $this
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
        return $this;
    }

    /**
     * The invoice recipient first name. Maximum length is 30 characters.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * The invoice recipient last name. Maximum length is 30 characters.
     *
     * @param string $last_name
     * 
     * @return $this
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
        return $this;
    }

    /**
     * The invoice recipient last name. Maximum length is 30 characters.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * The invoice recipient company business name. Maximum length is 100 characters.
     *
     * @param string $business_name
     * 
     * @return $this
     */
    public function setBusinessName($business_name)
    {
        $this->business_name = $business_name;
        return $this;
    }

    /**
     * The invoice recipient company business name. Maximum length is 100 characters.
     *
     * @return string
     */
    public function getBusinessName()
    {
        return $this->business_name;
    }

    /**
     * The invoice recipient address.
     *
     * @param \PayPal\Api\InvoiceAddress $address
     * 
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * The invoice recipient address.
     *
     * @return \PayPal\Api\InvoiceAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * The language in which the email was sent to the payer. Used only when the payer does not have a PayPal account.
     * Valid Values: ["da_DK", "de_DE", "en_AU", "en_GB", "en_US", "es_ES", "es_XC", "fr_CA", "fr_FR", "fr_XC", "he_IL", "id_ID", "it_IT", "ja_JP", "nl_NL", "no_NO", "pl_PL", "pt_BR", "pt_PT", "ru_RU", "sv_SE", "th_TH", "tr_TR", "zh_CN", "zh_HK", "zh_TW", "zh_XC"]
     *
     * @param string $language
     * 
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * The language in which the email was sent to the payer. Used only when the payer does not have a PayPal account.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Additional information, such as business hours. Maximum length is 40 characters.
     *
     * @param string $additional_info
     * 
     * @return $this
     */
    public function setAdditionalInfo($additional_info)
    {
        $this->additional_info = $additional_info;
        return $this;
    }

    /**
     * Additional information, such as business hours. Maximum length is 40 characters.
     *
     * @return string
     */
    public function getAdditionalInfo()
    {
        return $this->additional_info;
    }

    /**
     * Preferred notification channel of the payer. Email by default.
     * Valid Values: ["SMS", "EMAIL"]
     *
     * @param string $notification_channel
     * 
     * @return $this
     */
    public function setNotificationChannel($notification_channel)
    {
        $this->notification_channel = $notification_channel;
        return $this;
    }

    /**
     * Preferred notification channel of the payer. Email by default.
     *
     * @return string
     */
    public function getNotificationChannel()
    {
        return $this->notification_channel;
    }

    /**
     * Mobile Phone number of the recipient to which SMS will be sent if notification_channel is SMS.
     *
     * @param \PayPal\Api\Phone $phone
     * 
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Mobile Phone number of the recipient to which SMS will be sent if notification_channel is SMS.
     *
     * @return \PayPal\Api\Phone
     */
    public function getPhone()
    {
        return $this->phone;
    }

}
