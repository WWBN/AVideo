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

class Google_Service_ShoppingContent_AccountShippingCondition extends Google_Model
{
  public $deliveryLocationGroup;
  public $deliveryLocationId;
  public $deliveryPostalCode;
  protected $deliveryPostalCodeRangeType = 'Google_Service_ShoppingContent_AccountShippingPostalCodeRange';
  protected $deliveryPostalCodeRangeDataType = '';
  protected $priceMaxType = 'Google_Service_ShoppingContent_Price';
  protected $priceMaxDataType = '';
  public $shippingLabel;
  protected $weightMaxType = 'Google_Service_ShoppingContent_Weight';
  protected $weightMaxDataType = '';

  public function setDeliveryLocationGroup($deliveryLocationGroup)
  {
    $this->deliveryLocationGroup = $deliveryLocationGroup;
  }
  public function getDeliveryLocationGroup()
  {
    return $this->deliveryLocationGroup;
  }
  public function setDeliveryLocationId($deliveryLocationId)
  {
    $this->deliveryLocationId = $deliveryLocationId;
  }
  public function getDeliveryLocationId()
  {
    return $this->deliveryLocationId;
  }
  public function setDeliveryPostalCode($deliveryPostalCode)
  {
    $this->deliveryPostalCode = $deliveryPostalCode;
  }
  public function getDeliveryPostalCode()
  {
    return $this->deliveryPostalCode;
  }
  public function setDeliveryPostalCodeRange(Google_Service_ShoppingContent_AccountShippingPostalCodeRange $deliveryPostalCodeRange)
  {
    $this->deliveryPostalCodeRange = $deliveryPostalCodeRange;
  }
  public function getDeliveryPostalCodeRange()
  {
    return $this->deliveryPostalCodeRange;
  }
  public function setPriceMax(Google_Service_ShoppingContent_Price $priceMax)
  {
    $this->priceMax = $priceMax;
  }
  public function getPriceMax()
  {
    return $this->priceMax;
  }
  public function setShippingLabel($shippingLabel)
  {
    $this->shippingLabel = $shippingLabel;
  }
  public function getShippingLabel()
  {
    return $this->shippingLabel;
  }
  public function setWeightMax(Google_Service_ShoppingContent_Weight $weightMax)
  {
    $this->weightMax = $weightMax;
  }
  public function getWeightMax()
  {
    return $this->weightMax;
  }
}
