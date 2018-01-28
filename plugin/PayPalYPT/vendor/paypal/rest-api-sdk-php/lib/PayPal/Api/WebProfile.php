<?php

namespace PayPal\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Rest\ApiContext;
use PayPal\Transport\PayPalRestCall;
use PayPal\Validation\ArgumentValidator;

/**
 * Class WebProfile
 *
 * Payment web experience profile resource
 *
 * @package PayPal\Api
 *
 * @property string id
 * @property string name
 * @property bool temporary
 * @property \PayPal\Api\FlowConfig flow_config
 * @property \PayPal\Api\InputFields input_fields
 * @property \PayPal\Api\Presentation presentation
 */
class WebProfile extends PayPalResourceModel
{
    /**
     * The unique ID of the web experience profile.
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
     * The unique ID of the web experience profile.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * The web experience profile name. Unique for a specified merchant's profiles.
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
     * The web experience profile name. Unique for a specified merchant's profiles.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Indicates whether the profile persists for three hours or permanently. Set to `false` to persist the profile permanently. Set to `true` to persist the profile for three hours.
     *
     * @param bool $temporary
     * 
     * @return $this
     */
    public function setTemporary($temporary)
    {
        $this->temporary = $temporary;
        return $this;
    }

    /**
     * Indicates whether the profile persists for three hours or permanently. Set to `false` to persist the profile permanently. Set to `true` to persist the profile for three hours.
     *
     * @return bool
     */
    public function getTemporary()
    {
        return $this->temporary;
    }

    /**
     * Parameters for flow configuration.
     *
     * @param \PayPal\Api\FlowConfig $flow_config
     * 
     * @return $this
     */
    public function setFlowConfig($flow_config)
    {
        $this->flow_config = $flow_config;
        return $this;
    }

    /**
     * Parameters for flow configuration.
     *
     * @return \PayPal\Api\FlowConfig
     */
    public function getFlowConfig()
    {
        return $this->flow_config;
    }

    /**
     * Parameters for input fields customization.
     *
     * @param \PayPal\Api\InputFields $input_fields
     * 
     * @return $this
     */
    public function setInputFields($input_fields)
    {
        $this->input_fields = $input_fields;
        return $this;
    }

    /**
     * Parameters for input fields customization.
     *
     * @return \PayPal\Api\InputFields
     */
    public function getInputFields()
    {
        return $this->input_fields;
    }

    /**
     * Parameters for style and presentation.
     *
     * @param \PayPal\Api\Presentation $presentation
     * 
     * @return $this
     */
    public function setPresentation($presentation)
    {
        $this->presentation = $presentation;
        return $this;
    }

    /**
     * Parameters for style and presentation.
     *
     * @return \PayPal\Api\Presentation
     */
    public function getPresentation()
    {
        return $this->presentation;
    }

    /**
     * Creates a web experience profile. Pass the profile name and details in the JSON request body.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return CreateProfileResponse
     */
    public function create($apiContext = null, $restCall = null)
    {
        $payLoad = $this->toJSON();
        $json = self::executeCall(
            "/v1/payment-experience/web-profiles/",
            "POST",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new CreateProfileResponse();
        $ret->fromJson($json);
        return $ret;
    }

    /**
     * Updates a web experience profile. Pass the ID of the profile to the request URI and pass the profile details in the JSON request body. If your request omits any profile detail fields, the operation removes the previously set values for those fields.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return bool
     */
    public function update($apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($this->getId(), "Id");
        $payLoad = $this->toJSON();
        self::executeCall(
            "/v1/payment-experience/web-profiles/{$this->getId()}",
            "PUT",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        return true;
    }

    /**
     * Partially-updates a web experience profile. Pass the profile ID to the request URI. Pass a patch object with the operation, path of the profile location to update, and, if needed, a new value to complete the operation in the JSON request body.
     *
     * @param Patch[] $patch
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return bool
     */
    public function partial_update($patch, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($this->getId(), "Id");
        ArgumentValidator::validate($patch, 'patch');
        $payload = array();
        foreach ($patch as $patchObject) {
            $payload[] = $patchObject->toArray();
        }
        $payLoad = json_encode($payload);
        self::executeCall(
            "/v1/payment-experience/web-profiles/{$this->getId()}",
            "PATCH",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        return true;
    }

    /**
     * Shows details for a web experience profile, by ID.
     *
     * @param string $profileId
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return WebProfile
     */
    public static function get($profileId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($profileId, 'profileId');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/payment-experience/web-profiles/$profileId",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new WebProfile();
        $ret->fromJson($json);
        return $ret;
    }

    /**
     * Lists all web experience profiles for a merchant or subject.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return WebProfile[]
     */
    public static function get_list($apiContext = null, $restCall = null)
    {
        $payLoad = "";
        $json = self::executeCall(
            "/v1/payment-experience/web-profiles/",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        return WebProfile::getList($json);
    }

    /**
     * Deletes a web experience profile, by ID.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return bool
     */
    public function delete($apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($this->getId(), "Id");
        $payLoad = "";
        self::executeCall(
            "/v1/payment-experience/web-profiles/{$this->getId()}",
            "DELETE",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        return true;
    }

}
