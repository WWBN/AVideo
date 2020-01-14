<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class PaymentTerm
 *
 * The payment term of the invoice. If you specify `term_type`, you cannot specify `due_date` and vice versa.
 *
 * @package PayPal\Api
 *
 * @property string term_type
 * @property string due_date
 */
class PaymentTerm extends PayPalModel
{
    /**
     * The terms by which the invoice payment is due.
     * Valid Values: ["DUE_ON_RECEIPT", "DUE_ON_DATE_SPECIFIED", "NET_10", "NET_15", "NET_30", "NET_45", "NET_60", "NET_90", "NO_DUE_DATE"]
     *
     * @param string $term_type
     * 
     * @return $this
     */
    public function setTermType($term_type)
    {
        $this->term_type = $term_type;
        return $this;
    }

    /**
     * The terms by which the invoice payment is due.
     *
     * @return string
     */
    public function getTermType()
    {
        return $this->term_type;
    }

    /**
     * The date when the invoice payment is due. This date must be a future date. Date format is *yyyy*-*MM*-*dd* *z*, as defined in [Internet Date/Time Format](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @param string $due_date
     * 
     * @return $this
     */
    public function setDueDate($due_date)
    {
        $this->due_date = $due_date;
        return $this;
    }

    /**
     * The date when the invoice payment is due. This date must be a future date. Date format is *yyyy*-*MM*-*dd* *z*, as defined in [Internet Date/Time Format](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getDueDate()
    {
        return $this->due_date;
    }

}
