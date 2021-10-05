<?php

namespace PayPal\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Transport\PayPalRestCall;
use PayPal\Validation\ArgumentValidator;
use PayPal\Api\Template;
use PayPal\Rest\ApiContext;

/**
 * Class Templates
 *
 * List of templates belonging to merchant.
 *
 * @package PayPal\Api
 *
 * @property \PayPal\Api\Address[] addresses
 * @property string[] emails
 * @property \PayPal\Api\Phone[] phones
 * @property \PayPal\Api\Template[] templates
 * @property \PayPal\Api\Links[] links
 */
class Templates extends PayPalResourceModel
{
    /**
     * List of addresses in merchant's profile.
     *
     * @param \PayPal\Api\Address[] $addresses
     * 
     * @return $this
     */
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;
        return $this;
    }

    /**
     * List of addresses in merchant's profile.
     *
     * @return \PayPal\Api\Address[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Append Addresses to the list.
     *
     * @param \PayPal\Api\Address $address
     * @return $this
     */
    public function addAddress($address)
    {
        if (!$this->getAddresses()) {
            return $this->setAddresses(array($address));
        } else {
            return $this->setAddresses(
                array_merge($this->getAddresses(), array($address))
            );
        }
    }

    /**
     * Remove Addresses from the list.
     *
     * @param \PayPal\Api\Address $address
     * @return $this
     */
    public function removeAddress($address)
    {
        return $this->setAddresses(
            array_diff($this->getAddresses(), array($address))
        );
    }

    /**
     * List of emails in merchant's profile.
     *
     * @param string[] $emails
     * 
     * @return $this
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;
        return $this;
    }

    /**
     * List of emails in merchant's profile.
     *
     * @return string[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * Append Emails to the list.
     *
     * @param string $string
     * @return $this
     */
    public function addEmail($string)
    {
        if (!$this->getEmails()) {
            return $this->setEmails(array($string));
        } else {
            return $this->setEmails(
                array_merge($this->getEmails(), array($string))
            );
        }
    }

    /**
     * Remove Emails from the list.
     *
     * @param string $string
     * @return $this
     */
    public function removeEmail($string)
    {
        return $this->setEmails(
            array_diff($this->getEmails(), array($string))
        );
    }

    /**
     * List of phone numbers in merchant's profile.
     *
     * @param \PayPal\Api\Phone[] $phones
     * 
     * @return $this
     */
    public function setPhones($phones)
    {
        $this->phones = $phones;
        return $this;
    }

    /**
     * List of phone numbers in merchant's profile.
     *
     * @return \PayPal\Api\Phone[]
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * Append Phones to the list.
     *
     * @param \PayPal\Api\Phone $phone
     * @return $this
     */
    public function addPhone($phone)
    {
        if (!$this->getPhones()) {
            return $this->setPhones(array($phone));
        } else {
            return $this->setPhones(
                array_merge($this->getPhones(), array($phone))
            );
        }
    }

    /**
     * Remove Phones from the list.
     *
     * @param \PayPal\Api\Phone $phone
     * @return $this
     */
    public function removePhone($phone)
    {
        return $this->setPhones(
            array_diff($this->getPhones(), array($phone))
        );
    }

    /**
     * Array of templates.
     *
     * @param \PayPal\Api\Template[] $templates
     * 
     * @return $this
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;
        return $this;
    }

    /**
     * Array of templates.
     *
     * @return \PayPal\Api\Template[]
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Append Templates to the list.
     *
     * @param \PayPal\Api\Template $template
     * @return $this
     */
    public function addTemplate($template)
    {
        if (!$this->getTemplates()) {
            return $this->setTemplates(array($template));
        } else {
            return $this->setTemplates(
                array_merge($this->getTemplates(), array($template))
            );
        }
    }

    /**
     * Remove Templates from the list.
     *
     * @param \PayPal\Api\Template $template
     * @return $this
     */
    public function removeTemplate($template)
    {
        return $this->setTemplates(
            array_diff($this->getTemplates(), array($template))
        );
    }

    /**
     * Retrieve the details for a particular template by passing the template ID to the request URI.
     *
     * @deprecated Please use `Template::get()` instead.
     * @see Template::get
     * @param string $templateId
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Template
     */
    public static function get($templateId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($templateId, 'templateId');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/invoicing/templates/$templateId",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new Template();
        $ret->fromJson($json);
        return $ret;
    }

    /**
     * Retrieves the template information of the merchant.
     *
     * @param array $params
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Templates
     */
    public static function getAll($params = array(), $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($params, 'params');
        $payLoad = "";
        $allowedParams = array(
          'fields' => 1,
      );
        $json = self::executeCall(
            "/v1/invoicing/templates/" . "?" . http_build_query(array_intersect_key($params, $allowedParams)),
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new Templates();
        $ret->fromJson($json);
        return $ret;
    }
}
