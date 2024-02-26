<?php
namespace Razorpay\Api;

class Transfer extends Entity
{
    /**
     *  @param $id  Transfer ID
     */
    public function fetch($id)
    {
        return parent::fetch($id);
    }

    public function all($options = array())
    {
        if (isset($this->payment_id) === true)
        {
            $relativeUrl = 'payments/' . $this->payment_id. '/transfers';

            return $this->request('GET', $relativeUrl, $options);
        }

        return parent::all($options);
    }

    /**
     * Create a direct transfer from merchant's account to
     * any of the linked accounts, without linking it to a
     * payment
     */
    public function create($attributes = array())
    {
        return parent::create($attributes);
    }

    public function edit($attributes = null)
    {
        $entityUrl = $this->getEntityUrl() .  $this->id;

        return $this->request('PATCH', $entityUrl, $attributes);
    }

    /**
     * Create a reversal for a transfer
     */
    public function reverse($attributes = array())
    {
        $relativeUrl = $this->getEntityUrl() . $this->id . '/reversals';

        return $this->request('POST', $relativeUrl, $attributes);
    }

    /**
     * Fetches all reversals
     */
    public function reversals($attributes = array())
    {
        $relativeUrl = $this->getEntityUrl() . $this->id . '/reversals';

        return $this->request('GET', $relativeUrl, $attributes);
    }
}
