<?php

namespace Razorpay\Api;

class Item extends Entity
{

    public function create($attributes = array())
    {
        return parent::create($attributes);
    }

    public function fetch($id)
    {
        return parent::fetch($id);
    }

    public function edit($attributes = array())
    {
        $url = $this->getEntityUrl() . $this->id;

        return $this->request('PATCH', $url, $attributes);
    }

    public function all($options = array())
    {
        return parent::all($options);
    }

    public function delete()
    {
        $url = $this->getEntityUrl() . $this->id;

        return $this->request('DELETE', $url);
    }
}
