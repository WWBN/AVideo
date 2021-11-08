<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;
use PayPal\Validation\UrlValidator;

/**
 * Class Metadata
 *
 * Audit information for the resource.
 *
 * @package PayPal\Api
 *
 * @property string created_date
 * @property string created_by
 * @property string cancelled_date
 * @property string cancelled_by
 * @property string last_updated_date
 * @property string last_updated_by
 * @property string first_sent_date
 * @property string last_sent_date
 * @property string last_sent_by
 * @property string payer_view_url
 */
class Metadata extends PayPalModel
{
    /**
     * The date and time when the resource was created.
     *
     * @param string $created_date
     * 
     * @return $this
     */
    public function setCreatedDate($created_date)
    {
        $this->created_date = $created_date;
        return $this;
    }

    /**
     * The date and time when the resource was created.
     *
     * @return string
     */
    public function getCreatedDate()
    {
        return $this->created_date;
    }

    /**
     * The email address of the account that created the resource.
     *
     * @param string $created_by
     * 
     * @return $this
     */
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;
        return $this;
    }

    /**
     * The email address of the account that created the resource.
     *
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * The date and time when the resource was cancelled.
     *
     * @param string $cancelled_date
     * 
     * @return $this
     */
    public function setCancelledDate($cancelled_date)
    {
        $this->cancelled_date = $cancelled_date;
        return $this;
    }

    /**
     * The date and time when the resource was cancelled.
     *
     * @return string
     */
    public function getCancelledDate()
    {
        return $this->cancelled_date;
    }

    /**
     * The actor who cancelled the resource.
     *
     * @param string $cancelled_by
     * 
     * @return $this
     */
    public function setCancelledBy($cancelled_by)
    {
        $this->cancelled_by = $cancelled_by;
        return $this;
    }

    /**
     * The actor who cancelled the resource.
     *
     * @return string
     */
    public function getCancelledBy()
    {
        return $this->cancelled_by;
    }

    /**
     * The date and time when the resource was last edited.
     *
     * @param string $last_updated_date
     * 
     * @return $this
     */
    public function setLastUpdatedDate($last_updated_date)
    {
        $this->last_updated_date = $last_updated_date;
        return $this;
    }

    /**
     * The date and time when the resource was last edited.
     *
     * @return string
     */
    public function getLastUpdatedDate()
    {
        return $this->last_updated_date;
    }

    /**
     * The email address of the account that last edited the resource.
     *
     * @param string $last_updated_by
     * 
     * @return $this
     */
    public function setLastUpdatedBy($last_updated_by)
    {
        $this->last_updated_by = $last_updated_by;
        return $this;
    }

    /**
     * The email address of the account that last edited the resource.
     *
     * @return string
     */
    public function getLastUpdatedBy()
    {
        return $this->last_updated_by;
    }

    /**
     * The date and time when the resource was first sent.
     *
     * @param string $first_sent_date
     * 
     * @return $this
     */
    public function setFirstSentDate($first_sent_date)
    {
        $this->first_sent_date = $first_sent_date;
        return $this;
    }

    /**
     * The date and time when the resource was first sent.
     *
     * @return string
     */
    public function getFirstSentDate()
    {
        return $this->first_sent_date;
    }

    /**
     * The date and time when the resource was last sent.
     *
     * @param string $last_sent_date
     * 
     * @return $this
     */
    public function setLastSentDate($last_sent_date)
    {
        $this->last_sent_date = $last_sent_date;
        return $this;
    }

    /**
     * The date and time when the resource was last sent.
     *
     * @return string
     */
    public function getLastSentDate()
    {
        return $this->last_sent_date;
    }

    /**
     * The email address of the account that last sent the resource.
     *
     * @param string $last_sent_by
     * 
     * @return $this
     */
    public function setLastSentBy($last_sent_by)
    {
        $this->last_sent_by = $last_sent_by;
        return $this;
    }

    /**
     * The email address of the account that last sent the resource.
     *
     * @return string
     */
    public function getLastSentBy()
    {
        return $this->last_sent_by;
    }

    /**
     * URL representing the payer's view of the invoice.
     *
     * @param string $payer_view_url
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setPayerViewUrl($payer_view_url)
    {
        UrlValidator::validate($payer_view_url, "PayerViewUrl");
        $this->payer_view_url = $payer_view_url;
        return $this;
    }

    /**
     * URL representing the payer's view of the invoice.
     *
     * @return string
     */
    public function getPayerViewUrl()
    {
        return $this->payer_view_url;
    }

}
