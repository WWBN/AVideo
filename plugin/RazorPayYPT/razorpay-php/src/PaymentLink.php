<?php

namespace Razorpay\Api;

use Requests;


class PaymentLink extends Entity
{
    /**
     * Creates Payment link .
     *
     * @param array $attributes
     *
     * @return PaymentLink
     */
    public function create($attributes = array())
    {
        $attributes = json_encode($attributes);

        Request::addHeader('Content-Type', 'application/json');

        return parent::create($attributes);
    }

    /**
     * Fetches Payment link entity with given id
     *
     * @param string $id
     *
     * @return PaymentLink
     */
    public function fetch($id)
    {
        return parent::fetch($id);
    }

    /**
     * Fetches multiple Payment link with given query options
     *
     * @param array $options
     *
     * @return Collection
     */
    public function all($options = array())
    {
        return parent::all($options);
    }

    /**
     * Cancels Payment link
     *
     * @return PaymentLink
     */
    public function cancel()
    {
        $url = $this->getEntityUrl() . $this->id . '/cancel';

        return $this->request(Requests::POST, $url);
    }

    public function edit($attributes = array())
    {   
        $relativeUrl = $this->getEntityUrl() . $this->id;
        
        $attributes = json_encode($attributes);

        Request::addHeader('Content-Type', 'application/json');

        return $this->request('PATCH', $relativeUrl, $attributes);   
    }

    /**
     * Send/re-send notification with short url by given medium
     *
     * @param $medium - sms|email
     *
     * @return array
     */
    public function notifyBy($medium)
    {
        $url = $this->getEntityUrl() . $this->id . '/notify_by/' . $medium;
        $r = new Request();

        return $r->request(Requests::POST, $url);
    }

}
