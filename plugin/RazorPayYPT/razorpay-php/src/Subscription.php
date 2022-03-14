<?php

namespace Razorpay\Api;

class Subscription extends Entity
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

    public function cancel($attributes = array())
    {
        $relativeUrl = $this->getEntityUrl() . $this->id . '/cancel';

        return $this->request('POST', $relativeUrl, $attributes);
    }

    public function createAddon($attributes = array())
    {
        $relativeUrl = $this->getEntityUrl() . $this->id . '/addons';

        return $this->request('POST', $relativeUrl, $attributes);
    }

    /**
     * Create a Registration Link
     * @param  array $attributes
     * @return array
     */
    public function createSubscriptionRegistration($attributes = array())
    {
        $relativeUrl = 'subscription_registration/auth_links';

        return $this->request('POST', $relativeUrl, $attributes);
    }

    public function update($attributes = array())
    {
        $relativeUrl = $this->getEntityUrl() . $this->id;

        return $this->request('PATCH', $relativeUrl, $attributes);
    }

    public function pendingUpdate()
    {
        $relativeUrl = $this->getEntityUrl() . $this->id . '/retrieve_scheduled_changes';

        return $this->request('GET', $relativeUrl, null);
    }

    public function cancelScheduledChanges()
    {
        $relativeUrl = $this->getEntityUrl() . $this->id . '/cancel_scheduled_changes';

        return $this->request('POST', $relativeUrl, null);
    }

    public function pause($attributes = array())
    {
        $relativeUrl = $this->getEntityUrl() . $this->id.'/pause';

        return $this->request('POST', $relativeUrl, $attributes);
    }

    public function resume($attributes = array())
    {
        $relativeUrl = $this->getEntityUrl() . $this->id.'/resume';

        return $this->request('POST', $relativeUrl, $attributes);
    }

    public function deleteOffer($offerId)
    {
        $relativeUrl = $this->getEntityUrl() . $this->id.'/'.$offerId;

        return $this->request('DELETE', $relativeUrl);
    }

}
