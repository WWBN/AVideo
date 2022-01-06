<?php

namespace Razorpay\Api;

class VirtualAccount extends Entity
{
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

    public function close()
    {
        $relativeUrl = $this->getEntityUrl() . $this->id . '/close';


        return $this->request('POST', $relativeUrl);
    }

    public function payments($options = array())
    {
        $relativeUrl = $this->getEntityUrl() . $this->id . '/payments';

        return $this->request('GET', $relativeUrl, $options);
    }

    public function addReceiver($attributes = array())
    {
        $relativeUrl = $this->getEntityUrl() . $this->id . '/receivers';

        return $this->request('POST', $relativeUrl, $attributes); 
    }

    public function addAllowedPayer($attributes = array())
    {
        $relativeUrl = $this->getEntityUrl() . $this->id . '/allowed_payers';

        return $this->request('POST', $relativeUrl, $attributes);
    }

    public function deleteAllowedPayer($allowedPlayerId)
    {
        $relativeUrl = $this->getEntityUrl() . $this->id . '/allowed_payers/'.$allowedPlayerId;
 
        return $this->request('DELETE', $relativeUrl); 
    }
}