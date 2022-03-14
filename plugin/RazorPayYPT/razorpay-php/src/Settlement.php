<?php

namespace Razorpay\Api;

/**
 * Settlement related actions can be done from here
 */
class Settlement extends Entity
{
     /**
     * create Ondemand Settlemententity
     * @param  array $attributes
     * @return Settlement
     */
    public function createOndemandSettlement($attributes = array())
    {
        $relativeUrl = $this->getEntityUrl() ."ondemand" ;

        return $this->request('POST', $relativeUrl, $attributes);
    }
    
    /**
     * Fetch single settlement entity
     * @param  string $id
     * @return Settlement
     */
    public function fetch($id)
    {
        return parent::fetch($id);
    }

    /**
     * Get all settlements according to options
     * @param  array $options
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

     /**
     * Get Settlement Recon
     * @param  array  $options
     * @return array
     */
    public function settlementRecon($options = array())
    {
        $relativeUrl = $this->getEntityUrl() . 'recon/combined';

        return $this->request('GET', $relativeUrl, $options);
    }
     /**
     * fetch Ondemand Settlement by Id 
     * @param  string $id
     * @return array
     */
    public function fetchOndemandSettlementById()
    {
        $relativeUrl = $this->getEntityUrl(). "ondemand/" . $this->id ;
       
        return $this->request('GET', $relativeUrl);
    }
    /**
     * fetch all Ondemand Settlement 
     * @return array
     */
    public function fetchAllOndemandSettlement()
    {
        $relativeUrl = $this->getEntityUrl(). "ondemand/";
        
        return $this->request('GET', $relativeUrl);
    }
}

