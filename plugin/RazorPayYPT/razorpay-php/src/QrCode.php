<?php

namespace Razorpay\Api;

class QrCode extends Entity
{
    /**
     * Create QR code
     * @param array $attributes
     * @return Entity|QrCode
     */
    public function create($attributes = array())
    {
        $relativeUrl = "payments/". $this->getEntityUrl() ;

        return $this->request('POST', $relativeUrl, $attributes);
    }

    /**
     * Fetch QR code details based QR id
     * @param $id
     * @return Entity|QrCode
     */
    public function fetch($id)
    {
        $relativeUrl = "payments/". $this->getEntityUrl(). $id ;

        return $this->request('GET', $relativeUrl);
    }

    /**
     * Close the QR code based on id
     * @return Entity|QrCode
     */
    public function close()
    {
        $relativeUrl = "payments/{$this->getEntityUrl()}{$this->id}/close" ;

        return $this->request('POST', $relativeUrl);
    }

    /**
     * Fetch all QR code details
     * @param array $options
     * @return Entity|QrCode
     */
    public function all($options = array())
    {
        $relativeUrl = "payments/". $this->getEntityUrl();

        return $this->request('GET', $relativeUrl, $options);
    }

    /**
     * Fetch payments made to a QR Code based on QR id
     * @param array $options
     * @return Entity|QrCode
     */
    public function fetchAllPayments($options = array())
    {
        $relativeUrl = "payments/{$this->getEntityUrl()}{$this->id}/payments" ;

        return $this->request('GET', $relativeUrl, $options);
    }

}
