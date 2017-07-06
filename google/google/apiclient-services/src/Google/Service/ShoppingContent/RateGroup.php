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

class Google_Service_ShoppingContent_RateGroup extends Google_Collection
{
  protected $collection_key = 'subtables';
  public $applicableShippingLabels;
  protected $carrierRatesType = 'Google_Service_ShoppingContent_CarrierRate';
  protected $carrierRatesDataType = 'array';
  protected $mainTableType = 'Google_Service_ShoppingContent_Table';
  protected $mainTableDataType = '';
  protected $singleValueType = 'Google_Service_ShoppingContent_Value';
  protected $singleValueDataType = '';
  protected $subtablesType = 'Google_Service_ShoppingContent_Table';
  protected $subtablesDataType = 'array';

  public function setApplicableShippingLabels($applicableShippingLabels)
  {
    $this->applicableShippingLabels = $applicableShippingLabels;
  }
  public function getApplicableShippingLabels()
  {
    return $this->applicableShippingLabels;
  }
  public function setCarrierRates($carrierRates)
  {
    $this->carrierRates = $carrierRates;
  }
  public function getCarrierRates()
  {
    return $this->carrierRates;
  }
  public function setMainTable(Google_Service_ShoppingContent_Table $mainTable)
  {
    $this->mainTable = $mainTable;
  }
  public function getMainTable()
  {
    return $this->mainTable;
  }
  public function setSingleValue(Google_Service_ShoppingContent_Value $singleValue)
  {
    $this->singleValue = $singleValue;
  }
  public function getSingleValue()
  {
    return $this->singleValue;
  }
  public function setSubtables($subtables)
  {
    $this->subtables = $subtables;
  }
  public function getSubtables()
  {
    return $this->subtables;
  }
}
