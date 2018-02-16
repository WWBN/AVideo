<?php

namespace PayPal\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Validation\ArgumentValidator;
use PayPal\Api\WebhookList;
use PayPal\Rest\ApiContext;
use PayPal\Validation\UrlValidator;

/**
 * Class Webhook
 *
 * One or more webhook objects.
 *
 * @package PayPal\Api
 *
 * @property string id
 * @property string url
 * @property \PayPal\Api\WebhookEventType[] event_types
 */
class Webhook extends PayPalResourceModel
{
    /**
     * The ID of the webhook.
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
     * The ID of the webhook.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * The URL that is configured to listen on `localhost` for incoming `POST` notification messages that contain event information.
     *
     * @param string $url
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setUrl($url)
    {
        UrlValidator::validate($url, "Url");
        $this->url = $url;
        return $this;
    }

    /**
     * The URL that is configured to listen on `localhost` for incoming `POST` notification messages that contain event information.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * A list of up to ten events to which to subscribe your webhook. To subscribe to all events including new events as they are added, specify the asterisk (`*`) wildcard. To replace the `event_types` array, specify the `*` wildcard. To see all supported events, [list available events](#available-event-type.list).
     *
     * @param \PayPal\Api\WebhookEventType[] $event_types
     * 
     * @return $this
     */
    public function setEventTypes($event_types)
    {
        $this->event_types = $event_types;
        return $this;
    }

    /**
     * A list of up to ten events to which to subscribe your webhook. To subscribe to all events including new events as they are added, specify the asterisk (`*`) wildcard. To replace the `event_types` array, specify the `*` wildcard. To see all supported events, [list available events](#available-event-type.list).
     *
     * @return \PayPal\Api\WebhookEventType[]
     */
    public function getEventTypes()
    {
        return $this->event_types;
    }

    /**
     * Append EventTypes to the list.
     *
     * @param \PayPal\Api\WebhookEventType $webhookEventType
     * @return $this
     */
    public function addEventType($webhookEventType)
    {
        if (!$this->getEventTypes()) {
            return $this->setEventTypes(array($webhookEventType));
        } else {
            return $this->setEventTypes(
                array_merge($this->getEventTypes(), array($webhookEventType))
            );
        }
    }

    /**
     * Remove EventTypes from the list.
     *
     * @param \PayPal\Api\WebhookEventType $webhookEventType
     * @return $this
     */
    public function removeEventType($webhookEventType)
    {
        return $this->setEventTypes(
            array_diff($this->getEventTypes(), array($webhookEventType))
        );
    }

    /**
     * Subscribes your webhook listener to events. A successful call returns a [`webhook`](/docs/api/webhooks/#definition-webhook) object, which includes the webhook ID for later use.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Webhook
     */
    public function create($apiContext = null, $restCall = null)
    {
        $payLoad = $this->toJSON();
        $json = self::executeCall(
            "/v1/notifications/webhooks",
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
     * Shows details for a webhook, by ID.
     *
     * @param string $webhookId
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Webhook
     */
    public static function get($webhookId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($webhookId, 'webhookId');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/notifications/webhooks/$webhookId",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new Webhook();
        $ret->fromJson($json);
        return $ret;
    }

    /**
     * Retrieves all Webhooks for the application associated with access token.
     *
     * @deprecated Please use Webhook#getAllWithParams instead.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return WebhookList
     */
    public static function getAll($apiContext = null, $restCall = null)
    {
        return self::getAllWithParams(array(), $apiContext, $restCall);
    }

    /**
     * Lists all webhooks for an app.
     *
     * @param array $params
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return WebhookList
     */
    public static function getAllWithParams($params = array(), $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($params, 'params');
        $payLoad = "";
        $allowedParams = array(
            'anchor_type' => 1,
        );
        $json = self::executeCall(
            "/v1/notifications/webhooks?" . http_build_query(array_intersect_key($params, $allowedParams)),
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new WebhookList();
        $ret->fromJson($json);
        return $ret;
    }

    /**
     * Replaces webhook fields with new values. Pass a `json_patch` object with `replace` operation and `path`, which is `/url` for a URL or `/event_types` for events. The `value` is either the URL or a list of events.
     *
     * @param PatchRequest $patchRequest
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Webhook
     */
    public function update($patchRequest, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($this->getId(), "Id");
        ArgumentValidator::validate($patchRequest, 'patchRequest');
        $payLoad = $patchRequest->toJSON();
        $json = self::executeCall(
            "/v1/notifications/webhooks/{$this->getId()}",
            "PATCH",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $this->fromJson($json);
        return $this;
    }

    /**
     * Deletes a webhook, by ID.
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
            "/v1/notifications/webhooks/{$this->getId()}",
            "DELETE",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        return true;
    }

}
