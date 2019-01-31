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

class Google_Service_ShoppingContent_AccountShippingCarrierRate extends Google_Model
{
  public $carrier;
  public $carrierService;
  protected $modifierFlatRateType = 'Google_Service_ShoppingContent_Price';
  protected $modifierFlatRateDataType = '';
  public $modifierPercent;
  public $name;
  public $saleCountry;
  public $shippingOrigin;

  public function setCarrier($carrier)
  {
    $this->carrier = $carrier;
  }
  public function getCarrier()
  {
    return $this->carrier;
  }
  public function setCarrierService($carrierService)
  {
    $this->carrierService = $carrierService;
  }
  public function getCarrierService()
  {
    return $this->carrierService;
  }
  public function setModifierFlatRate(Google_Service_ShoppingContent_Price $modifierFlatRate)
  {
    $this->modifierFlatRate = $modifierFlatRate;
  }
  public function getModifierFlatRate()
  {
    return $this->modifierFlatRate;
  }
  public function setModifierPercent($modifierPercent)
  {
    $this->modifierPercent = $modifierPercent;
  }
  public function getModifierPercent()
  {
    return $this->modifierPercent;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setSaleCountry($saleCountry)
  {
    $this->saleCountry = $saleCountry;
  }
  public function getSaleCountry()
  {
    return $this->saleCountry;
  }
  public function setShippingOrigin($shippingOrigin)
  {
    $this->shippingOrigin = $shippingOrigin;
  }
  public function getShippingOrigin()
  {
    return $this->shippingOrigin;
  }
}
