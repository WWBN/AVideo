<?php

namespace Razorpay\Api;

class Order extends Entity
{
    /**
     * @param $id Order id description
     */
    public function create($attributes = array())
    {
        return parent::create($attributes);
    }

    public function fetch($id)
    {
        return parent::fetch($id);
    }

    public function all($options = array())
    {
        return parent::all($options);
    }

    public function payments()
    {
        $relativeUrl = $this->getEntityUrl().$this->id.'/payments';

        return $this->request('GET', $relativeUrl);
    }
}
