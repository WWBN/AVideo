<?php

namespace Razorpay\Api;

class Refund extends Entity
{
    /**
     * @param $id Refund id
     */
    public function fetch($id)
    {
        return parent::fetch($id);
    }

    public function create($attributes = array())
    {
        return parent::create($attributes);
    }

    public function all($options = array())
    {
        return parent::all($options);
    }

    public function edit($attributes = array())
    {
        $url = $this->getEntityUrl() . $this->id;

        return $this->request('PATCH', $url, $attributes);
    }

    public function refund($options = array())
    {
        $relativeUrl = $this->getEntityUrl() . $this->id . '/refund';

        return $this->request('POST', $relativeUrl, $options);
    }
}