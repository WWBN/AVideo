<?php

namespace PayPal\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Rest\ApiContext;
use PayPal\Transport\PayPalRestCall;
use PayPal\Validation\ArgumentValidator;

/**
 * Class Template
 *
 * Invoicing Template
 *
 * @package PayPal\Api
 *
 * @property string template_id
 * @property string name
 * @property bool default
 * @property \PayPal\Api\TemplateData template_data
 * @property \PayPal\Api\TemplateSettings[] settings
 * @property string unit_of_measure
 * @property bool custom
 */
class Template extends PayPalResourceModel
{
    /**
     * Unique identifier id of the template.
     *
     * @param string $template_id
     * 
     * @return $this
     */
    public function setTemplateId($template_id)
    {
        $this->template_id = $template_id;
        return $this;
    }

    /**
     * Unique identifier id of the template.
     *
     * @return string
     */
    public function getTemplateId()
    {
        return $this->template_id;
    }

    /**
     * Name of the template.
     *
     * @param string $name
     * 
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Name of the template.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Indicates that this template is merchant's default. There can be only one template which can be a default.
     *
     * @param bool $default
     * 
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Indicates that this template is merchant's default. There can be only one template which can be a default.
     *
     * @return bool
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Customized invoice data which is saved as template
     *
     * @param \PayPal\Api\TemplateData $template_data
     * 
     * @return $this
     */
    public function setTemplateData($template_data)
    {
        $this->template_data = $template_data;
        return $this;
    }

    /**
     * Customized invoice data which is saved as template
     *
     * @return \PayPal\Api\TemplateData
     */
    public function getTemplateData()
    {
        return $this->template_data;
    }

    /**
     * Settings for each template
     *
     * @param \PayPal\Api\TemplateSettings[] $settings
     * 
     * @return $this
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * Settings for each template
     *
     * @return \PayPal\Api\TemplateSettings[]
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Append Settings to the list.
     *
     * @param \PayPal\Api\TemplateSettings $templateSettings
     * @return $this
     */
    public function addSetting($templateSettings)
    {
        if (!$this->getSettings()) {
            return $this->setSettings(array($templateSettings));
        } else {
            return $this->setSettings(
                array_merge($this->getSettings(), array($templateSettings))
            );
        }
    }

    /**
     * Remove Settings from the list.
     *
     * @param \PayPal\Api\TemplateSettings $templateSettings
     * @return $this
     */
    public function removeSetting($templateSettings)
    {
        return $this->setSettings(
            array_diff($this->getSettings(), array($templateSettings))
        );
    }

    /**
     * Unit of measure for the template, possible values are Quantity, Hours, Amount.
     *
     * @param string $unit_of_measure
     * 
     * @return $this
     */
    public function setUnitOfMeasure($unit_of_measure)
    {
        $this->unit_of_measure = $unit_of_measure;
        return $this;
    }

    /**
     * Unit of measure for the template, possible values are Quantity, Hours, Amount.
     *
     * @return string
     */
    public function getUnitOfMeasure()
    {
        return $this->unit_of_measure;
    }

    /**
     * Indicates whether this is a custom template created by the merchant. Non custom templates are system generated
     *
     * @param bool $custom
     * 
     * @return $this
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;
        return $this;
    }

    /**
     * Indicates whether this is a custom template created by the merchant. Non custom templates are system generated
     *
     * @return bool
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * Retrieve the details for a particular template by passing the template ID to the request URI.
     *
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
     * Delete a particular template by passing the template ID to the request URI.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return bool
     */
    public function delete($apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($this->getTemplateId(), "Id");
        $payLoad = "";
        self::executeCall(
            "/v1/invoicing/templates/{$this->getTemplateId()}",
            "DELETE",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        return true;
    }

    /**
     * Creates a template.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Template
     */
    public function create($apiContext = null, $restCall = null)
    {
        $json = self::executeCall(
            "/v1/invoicing/templates",
            "POST",
            $this->toJSON(),
            null,
            $apiContext,
            $restCall
        );
        $this->fromJson($json);
        return $this;
    }

    /**
     * Update an existing template by passing the template ID to the request URI. In addition, pass a complete template object in the request JSON. Partial updates are not supported.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Template
     */
    public function update($apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($this->getTemplateId(), "Id");
        $payLoad = $this->toJSON();
        $json = self::executeCall(
            "/v1/invoicing/templates/{$this->getTemplateId()}",
            "PUT",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $this->fromJson($json);
        return $this;
    }

}
