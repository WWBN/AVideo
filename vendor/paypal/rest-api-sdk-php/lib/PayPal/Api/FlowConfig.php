<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;
use PayPal\Validation\UrlValidator;

/**
 * Class FlowConfig
 *
 * Parameters for flow configuration.
 *
 * @package PayPal\Api
 *
 * @property string landing_page_type
 * @property string bank_txn_pending_url
 * @property string user_action
 * @property string return_uri_http_method
 */
class FlowConfig extends PayPalModel
{
    /**
     * The type of landing page to display on the PayPal site for user checkout. Set to `Billing` to use the non-PayPal account landing page. Set to `Login` to use the PayPal account login landing page.
     *
     * @param string $landing_page_type
     * 
     * @return $this
     */
    public function setLandingPageType($landing_page_type)
    {
        $this->landing_page_type = $landing_page_type;
        return $this;
    }

    /**
     * The type of landing page to display on the PayPal site for user checkout. Set to `Billing` to use the non-PayPal account landing page. Set to `Login` to use the PayPal account login landing page.
     *
     * @return string
     */
    public function getLandingPageType()
    {
        return $this->landing_page_type;
    }

    /**
     * The merchant site URL to display after a bank transfer payment. Valid for only the Giropay or bank transfer payment method in Germany.
     *
     * @param string $bank_txn_pending_url
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setBankTxnPendingUrl($bank_txn_pending_url)
    {
        UrlValidator::validate($bank_txn_pending_url, "BankTxnPendingUrl");
        $this->bank_txn_pending_url = $bank_txn_pending_url;
        return $this;
    }

    /**
     * The merchant site URL to display after a bank transfer payment. Valid for only the Giropay or bank transfer payment method in Germany.
     *
     * @return string
     */
    public function getBankTxnPendingUrl()
    {
        return $this->bank_txn_pending_url;
    }

    /**
     * Defines whether buyers can complete purchases on the PayPal or merchant website.
     *
     * @param string $user_action
     * 
     * @return $this
     */
    public function setUserAction($user_action)
    {
        $this->user_action = $user_action;
        return $this;
    }

    /**
     * Defines whether buyers can complete purchases on the PayPal or merchant website.
     *
     * @return string
     */
    public function getUserAction()
    {
        return $this->user_action;
    }

    /**
     * Defines the HTTP method to use to redirect the user to a return URL. A valid value is `GET` or `POST`.
     *
     * @param string $return_uri_http_method
     * 
     * @return $this
     */
    public function setReturnUriHttpMethod($return_uri_http_method)
    {
        $this->return_uri_http_method = $return_uri_http_method;
        return $this;
    }

    /**
     * Defines the HTTP method to use to redirect the user to a return URL. A valid value is `GET` or `POST`.
     *
     * @return string
     */
    public function getReturnUriHttpMethod()
    {
        return $this->return_uri_http_method;
    }

}
