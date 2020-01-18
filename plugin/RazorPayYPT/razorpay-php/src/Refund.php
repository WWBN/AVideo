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
}