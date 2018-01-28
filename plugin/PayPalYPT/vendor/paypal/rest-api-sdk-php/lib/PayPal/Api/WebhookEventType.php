<?php

namespace PayPal\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Validation\ArgumentValidator;
use PayPal\Api\WebhookEventTypeList;
use PayPal\Rest\ApiContext;

/**
 * Class WebhookEventType
 *
 * A list of events.
 *
 * @package PayPal\Api
 *
 * @property string name
 * @property string description
 * @property string status
 */
class WebhookEventType extends PayPalResourceModel
{
    /**
     * The unique event name.
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
     * The unique event name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * A human-readable description of the event.
     *
     * @param string $description
     * 
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * A human-readable description of the event.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * The status of a webhook event.
     *
     * @param string $status
     * 
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The status of a webhook event.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Lists event subscriptions for a webhook, by ID.
     *
     * @param string $webhookId
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return WebhookEventTypeList
     */
    public static function subscribedEventTypes($webhookId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($webhookId, 'webhookId');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/notifications/webhooks/$webhookId/event-types",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new WebhookEventTypeList();
        $ret->fromJson($json);
        return $ret;
    }

    /**
     * Lists available events to which any webhook can subscribe. For a list of supported events, see [Webhook events](/docs/integration/direct/rest/webhooks/webhook-events/).
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return WebhookEventTypeList
     */
    public static function availableEventTypes($apiContext = null, $restCall = null)
    {
        $payLoad = "";
        $json = self::executeCall(
            "/v1/notifications/webhooks-event-types",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new WebhookEventTypeList();
        $ret->fromJson($json);
        return $ret;
    }

}
