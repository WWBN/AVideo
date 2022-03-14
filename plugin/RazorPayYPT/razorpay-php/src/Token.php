<?php

namespace Razorpay\Api;

class Token extends Entity
{
    /**
     * @param $id Token id
     */
    public function fetch($id)
    {
        $relativeUrl = 'customers/'.$this->customer_id.'/'.$this->getEntityUrl().$id;

        return $this->request('GET', $relativeUrl);
    }

    public function all($options = array())
    {
        $relativeUrl = 'customers/'.$this->customer_id.'/'.$this->getEntityUrl();

        return $this->request('GET', $relativeUrl, $options);
    }

    public function delete($id)
    {
        $relativeUrl = 'customers/'.$this->customer_id.'/'.$this->getEntityUrl().$id;

        return $this->request('DELETE', $relativeUrl);
    }
}
