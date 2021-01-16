<?php
/*
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

class Google_Service_ShoppingContent_AccountShipping extends Google_Collection
{
  protected $collection_key = 'services';
  public $accountId;
  protected $carrierRatesType = 'Google_Service_ShoppingContent_AccountShippingCarrierRate';
  protected $carrierRatesDataType = 'array';
  public $kind;
  protected $locationGroupsType = 'Google_Service_ShoppingContent_AccountShippingLocationGroup';
  protected $locationGroupsDataType = 'array';
  protected $rateTablesType = 'Google_Service_ShoppingContent_AccountShippingRateTable';
  protected $rateTablesDataType = 'array';
  protected $servicesType = 'Google_Service_ShoppingContent_AccountShippingShippingService';
  protected $servicesDataType = 'array';

  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setCarrierRates($carrierRates)
  {
    $this->carrierRates = $carrierRates;
  }
  public function getCarrierRates()
  {
    return $this->carrierRates;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLocationGroups($locationGroups)
  {
    $this->locationGroups = $locationGroups;
  }
  public function getLocationGroups()
  {
    return $this->locationGroups;
  }
  public function setRateTables($rateTables)
  {
    $this->rateTables = $rateTables;
  }
  public function getRateTables()
  {
    return $this->rateTables;
  }
  public function setServices($services)
  {
    $this->services = $services;
  }
  public function getServices()
  {
    return $this->services;
  }
}
