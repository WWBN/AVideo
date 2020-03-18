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

class Google_Service_ShoppingContent_AccountShippingShippingServiceCalculationMethod extends Google_Model
{
  public $carrierRate;
  public $excluded;
  protected $flatRateType = 'Google_Service_ShoppingContent_Price';
  protected $flatRateDataType = '';
  public $percentageRate;
  public $rateTable;

  public function setCarrierRate($carrierRate)
  {
    $this->carrierRate = $carrierRate;
  }
  public function getCarrierRate()
  {
    return $this->carrierRate;
  }
  public function setExcluded($excluded)
  {
    $this->excluded = $excluded;
  }
  public function getExcluded()
  {
    return $this->excluded;
  }
  public function setFlatRate(Google_Service_ShoppingContent_Price $flatRate)
  {
    $this->flatRate = $flatRate;
  }
  public function getFlatRate()
  {
    return $this->flatRate;
  }
  public function setPercentageRate($percentageRate)
  {
    $this->percentageRate = $percentageRate;
  }
  public function getPercentageRate()
  {
    return $this->percentageRate;
  }
  public function setRateTable($rateTable)
  {
    $this->rateTable = $rateTable;
  }
  public function getRateTable()
  {
    return $this->rateTable;
  }
}
