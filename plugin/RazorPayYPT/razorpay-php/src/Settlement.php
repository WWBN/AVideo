<?php

namespace Razorpay\Api;

/**
 * Settlement related actions can be done from here
 */
class Settlement extends Entity
{
    /**
     * Fetch single settlement entity
     * @param  string      $id
     * @return Settlement
     */
    public function fetch($id)
    {
        return parent::fetch($id);
    }

    /**
     * Get all settlements according to options
     * @param  array       $options
     * @return Collection
     */
    public function all($options = array())
    {
        return parent::all($options);
    }

    /**
     * Get combined report of settlements
     * @param  array  $options
     * @return array
     */
    public function reports($options = array())
    {
        $relativeUrl = $this->getEntityUrl() . 'report/combined';

        return $this->request('GET', $relativeUrl, $options);
    }
}

