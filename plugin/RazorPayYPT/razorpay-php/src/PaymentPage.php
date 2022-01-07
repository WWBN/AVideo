<?php

namespace Razorpay\Api;

class PaymentPage extends Entity
{

    public function fetch($id)
    {
        return parent::fetch($id);
    }

    public function all($options = array())
    {
        return parent::all($options);
    }

    public function activate($id)
    {
        $relativeUrl = $this->getEntityUrl() . $id . '/activate';

        return $this->request('PATCH', $relativeUrl);
    }

    public function deactivate($id)
    {
        $relativeUrl = $this->getEntityUrl() . $id . '/deactivate';

        return $this->request('PATCH', $relativeUrl);
    }
}