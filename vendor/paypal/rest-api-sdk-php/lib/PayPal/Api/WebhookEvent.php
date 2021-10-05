<?php

namespace PayPal\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use PayPal\Transport\PayPalRestCall;
use PayPal\Validation\ArgumentValidator;
use PayPal\Validation\JsonValidator;

/**
 * Class WebhookEvent
 *
 * A webhook event notification.
 *
 * @package PayPal\Api
 *
 * @property string id
 * @property string create_time
 * @property string resource_type
 * @property string event_version
 * @property string event_type
 * @property string summary
 * @property \PayPal\Common\PayPalModel resource
 * @property string status
 * @property mixed[] transmissions
 */
class WebhookEvent extends PayPalResourceModel
{
    /**
     * The ID of the webhook event notification.
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
     * The ID of the webhook event notification.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * The date and time when the webhook event notification was created.
     *
     * @param string $create_time
     * 
     * @return $this
     */
    public function setCreateTime($create_time)
    {
        $this->create_time = $create_time;
        return $this;
    }

    /**
     * The date and time when the webhook event notification was created.
     *
     * @return string
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * The name of the resource related to the webhook notification event.
     *
     * @param string $resource_type
     * 
     * @return $this
     */
    public function setResourceType($resource_type)
    {
        $this->resource_type = $resource_type;
        return $this;
    }

    /**
     * The name of the resource related to the webhook notification event.
     *
     * @return string
     */
    public function getResourceType()
    {
        return $this->resource_type;
    }

    /**
     * The version of the event.
     *
     * @param string $event_version
     * 
     * @return $this
     */
    public function setEventVersion($event_version)
    {
        $this->event_version = $event_version;
        return $this;
    }

    /**
     * The version of the event.
     *
     * @return string
     */
    public function getEventVersion()
    {
        return $this->event_version;
    }

    /**
     * The event that triggered the webhook event notification.
     *
     * @param string $event_type
     * 
     * @return $this
     */
    public function setEventType($event_type)
    {
        $this->event_type = $event_type;
        return $this;
    }

    /**
     * The event that triggered the webhook event notification.
     *
     * @return string
     */
    public function getEventType()
    {
        return $this->event_type;
    }

    /**
     * A summary description for the event notification. For example, `A payment authorization was created.`
     *
     * @param string $summary
     * 
     * @return $this
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
        return $this;
    }

    /**
     * A summary description for the event notification. For example, `A payment authorization was created.`
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * The resource that triggered the webhook event notification.
     *
     * @param \PayPal\Common\PayPalModel $resource
     * 
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * The resource that triggered the webhook event notification.
     *
     * @return \PayPal\Common\PayPalModel
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Validates Received Event from Webhook, and returns the webhook event object. Because security verifications by verifying certificate chain is not enabled in PHP yet,
     * we need to fallback to default behavior of retrieving the ID attribute of the data, and make a separate GET call to PayPal APIs, to retrieve the data.
     * This is important to do again, as hacker could have faked the data, and the retrieved data cannot be trusted without either doing client side security validation, or making a separate call
     * to PayPal APIs to retrieve the actual data. This limits the hacker to mimick a fake data, as hacker wont be able to predict the Id correctly.
     *
     * NOTE: PLEASE DO NOT USE THE DATA PROVIDED IN WEBHOOK DIRECTLY, AS HACKER COULD PASS IN FAKE DATA. IT IS VERY IMPORTANT THAT YOU RETRIEVE THE ID AND MAKE A SEPARATE CALL TO PAYPAL API.
     *
     * @deprecated Please use `VerifyWebhookSignature->post()` instead.
     *
     * @param string     $body
     * @param ApiContext $apiContext
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return WebhookEvent
     * @throws \InvalidArgumentException if input arguments are incorrect, or Id is not found.
     * @throws PayPalConnectionException if any exception from PayPal APIs other than not found is sent.
     */
    public static function validateAndGetReceivedEvent($body, $apiContext = null, $restCall = null)
    {
        if ($body == null | empty($body)){
            throw new \InvalidArgumentException("Body cannot be null or empty");
        }
        if (!JsonValidator::validate($body, true)) {
            throw new \InvalidArgumentException("Request Body is not a valid JSON.");
        }
        $object = new WebhookEvent($body);
        if ($object->getId() == null) {
            throw new \InvalidArgumentException("Id attribute not found in JSON. Possible reason could be invalid JSON Object");
        }
        try {
            return self::get($object->getId(), $apiContext, $restCall);
        } catch(PayPalConnectionException $ex) {
            if ($ex->getCode() == 404) {
                // It means that the given webhook event Id is not found for this merchant.
                throw new \InvalidArgumentException("Webhook Event Id provided in the data is incorrect. This could happen if anyone other than PayPal is faking the incoming webhook data.");
            }
            throw $ex;
        }
    }

    /**
     * Retrieves the Webhooks event resource identified by event_id. Can be used to retrieve the payload for an event.
     *
     * @param string $eventId
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return WebhookEvent
     */
    public static function get($eventId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($eventId, 'eventId');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/notifications/webhooks-events/$eventId",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new WebhookEvent();
        $ret->fromJson($json);
        return $ret;
    }

    /**
     * Resends a webhook event notification, by ID. Any pending notifications are not resent.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return WebhookEvent
     */
    public function resend($apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($this->getId(), "Id");
        $payLoad = "";
        $json = self::executeCall(
            "/v1/notifications/webhooks-events/{$this->getId()}/resend",
            "POST",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $this->fromJson($json);
        return $this;
    }

    /**
     * Lists webhook event notifications. Use query parameters to filter the response.
     *
     * @param array $params
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return WebhookEventList
     */
    public static function all($params, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($params, 'params');
        $payLoad = "";
        $allowedParams = array(
          'page_size' => 1,
          'start_time' => 1,
          'end_time' => 1,
          'transaction_id' => 1,
          'event_type' => 1,
      );
        $json = self::executeCall(
            "/v1/notifications/webhooks-events" . "?" . http_build_query(array_intersect_key($params, $allowedParams)),
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new WebhookEventList();
        $ret->fromJson($json);
        return $ret;
    }

}
