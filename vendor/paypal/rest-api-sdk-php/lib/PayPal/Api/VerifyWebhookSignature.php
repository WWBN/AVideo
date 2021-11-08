<?php

namespace PayPal\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Validation\ArgumentValidator;
use PayPal\Api\VerifyWebhookSignatureResponse;
use PayPal\Rest\ApiContext;
use PayPal\Validation\UrlValidator;

/**
 * Class VerifyWebhookSignature
 *
 * Verify webhook signature.
 *
 * @package PayPal\Api
 *
 * @property string auth_algo
 * @property string cert_url
 * @property string transmission_id
 * @property string transmission_sig
 * @property string transmission_time
 * @property string webhook_id
 * @property \PayPal\Api\WebhookEvent webhook_event
 */
class VerifyWebhookSignature extends PayPalResourceModel
{
    /**
     * The algorithm that PayPal uses to generate the signature and that you can use to verify the signature. Extract this value from the `PAYPAL-AUTH-ALGO` response header, which is received with the webhook notification.
     *
     * @param string $auth_algo
     *
     * @return $this
     */
    public function setAuthAlgo($auth_algo)
    {
        $this->auth_algo = $auth_algo;
        return $this;
    }

    /**
     * The algorithm that PayPal uses to generate the signature and that you can use to verify the signature. Extract this value from the `PAYPAL-AUTH-ALGO` response header, which is received with the webhook notification.
     *
     * @return string
     */
    public function getAuthAlgo()
    {
        return $this->auth_algo;
    }

    /**
     * The X.509 public key certificate. Download the certificate from this URL and use it to verify the signature. Extract this value from the `PAYPAL-CERT-URL` response header, which is received with the webhook notification.
     *
     * @param string $cert_url
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setCertUrl($cert_url)
    {
        UrlValidator::validate($cert_url, "CertUrl");
        $this->cert_url = $cert_url;
        return $this;
    }

    /**
     * The X.509 public key certificate. Download the certificate from this URL and use it to verify the signature. Extract this value from the `PAYPAL-CERT-URL` response header, which is received with the webhook notification.
     *
     * @return string
     */
    public function getCertUrl()
    {
        return $this->cert_url;
    }

    /**
     * The ID of the HTTP transmission. Contained in the `PAYPAL-TRANSMISSION-ID` header of the notification message.
     *
     * @param string $transmission_id
     *
     * @return $this
     */
    public function setTransmissionId($transmission_id)
    {
        $this->transmission_id = $transmission_id;
        return $this;
    }

    /**
     * The ID of the HTTP transmission. Contained in the `PAYPAL-TRANSMISSION-ID` header of the notification message.
     *
     * @return string
     */
    public function getTransmissionId()
    {
        return $this->transmission_id;
    }

    /**
     * The PayPal-generated asymmetric signature. Extract this value from the `PAYPAL-TRANSMISSION-SIG` response header, which is received with the webhook notification.
     *
     * @param string $transmission_sig
     *
     * @return $this
     */
    public function setTransmissionSig($transmission_sig)
    {
        $this->transmission_sig = $transmission_sig;
        return $this;
    }

    /**
     * The PayPal-generated asymmetric signature. Extract this value from the `PAYPAL-TRANSMISSION-SIG` response header, which is received with the webhook notification.
     *
     * @return string
     */
    public function getTransmissionSig()
    {
        return $this->transmission_sig;
    }

    /**
     * The date and time of the HTTP transmission. Contained in the `PAYPAL-TRANSMISSION-TIME` header of the notification message.
     *
     * @param string $transmission_time
     *
     * @return $this
     */
    public function setTransmissionTime($transmission_time)
    {
        $this->transmission_time = $transmission_time;
        return $this;
    }

    /**
     * The date and time of the HTTP transmission. Contained in the `PAYPAL-TRANSMISSION-TIME` header of the notification message.
     *
     * @return string
     */
    public function getTransmissionTime()
    {
        return $this->transmission_time;
    }

    /**
     * The ID of the webhook as configured in your Developer Portal account.
     *
     * @param string $webhook_id
     *
     * @return $this
     */
    public function setWebhookId($webhook_id)
    {
        $this->webhook_id = $webhook_id;
        return $this;
    }

    /**
     * The ID of the webhook as configured in your Developer Portal account.
     *
     * @return string
     */
    public function getWebhookId()
    {
        return $this->webhook_id;
    }

    /**
     * The webhook notification, which is the content of the HTTP `POST` request body.
     * @deprecated Please use setRequestBody($request_body) instead.
     * @param \PayPal\Api\WebhookEvent $webhook_event
     *
     * @return $this
     */
    public function setWebhookEvent($webhook_event)
    {
        $this->webhook_event = $webhook_event;
        return $this;
    }

    /**
     * The webhook notification, which is the content of the HTTP `POST` request body.
     *
     * @return \PayPal\Api\WebhookEvent
     */
    public function getWebhookEvent()
    {
        return $this->webhook_event;
    }

    /**
     * The content of the HTTP `POST` request body of the webhook notification you received as a string.
     *
     * @param string $request_body
     *
     * @return $this
     */
    public function setRequestBody($request_body)
    {
        $this->request_body = $request_body;
        return $this;
    }

    /**
     * The content of the HTTP `POST` request body of the webhook notification you received as a string.
     *
     * @return string
     */
    public function getRequestBody()
    {
        return $this->request_body;
    }

    /**
     * Verifies a webhook signature.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return VerifyWebhookSignatureResponse
     */
    public function post($apiContext = null, $restCall = null)
    {
        $payLoad = $this->toJSON();

        $json = self::executeCall(
            "/v1/notifications/verify-webhook-signature",
            "POST",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new VerifyWebhookSignatureResponse();
        $ret->fromJson($json);
        return $ret;
    }

    public function toJSON($options = 0)
    {
        if (!is_null($this->request_body)) {
            $valuesToEncode = $this->toArray();
            unset($valuesToEncode['webhook_event']);
            unset($valuesToEncode['request_body']);

            $payLoad = "{";
            foreach ($valuesToEncode as $field => $value) {
                $payLoad .= "\"$field\": \"$value\",";
            }
            $payLoad .= "\"webhook_event\": $this->request_body";
            $payLoad .= "}";
            return $payLoad;
        } else {
            $payLoad = parent::toJSON($options);
            return $payLoad;
        }
    }
}
