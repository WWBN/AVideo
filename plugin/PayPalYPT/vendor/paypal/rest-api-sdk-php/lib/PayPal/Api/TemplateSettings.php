<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class TemplateSettings
 *
 * Settings per template
 *
 * @package PayPal\Api
 *
 * @property string field_name
 * @property \PayPal\Api\TemplateSettingsMetadata display_preference
 */
class TemplateSettings extends PayPalModel
{
    /**
     * The field name (for any field in template_data) for which the corresponding display preferences will be mapped to.
     *
     * @param string $field_name
     * 
     * @return $this
     */
    public function setFieldName($field_name)
    {
        $this->field_name = $field_name;
        return $this;
    }

    /**
     * The field name (for any field in template_data) for which the corresponding display preferences will be mapped to.
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->field_name;
    }

    /**
     * Settings metadata for each field.
     *
     * @param \PayPal\Api\TemplateSettingsMetadata $display_preference
     * 
     * @return $this
     */
    public function setDisplayPreference($display_preference)
    {
        $this->display_preference = $display_preference;
        return $this;
    }

    /**
     * Settings metadata for each field.
     *
     * @return \PayPal\Api\TemplateSettingsMetadata
     */
    public function getDisplayPreference()
    {
        return $this->display_preference;
    }

}
