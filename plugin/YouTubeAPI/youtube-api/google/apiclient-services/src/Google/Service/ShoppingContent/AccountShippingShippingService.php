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

class Google_Service_ShoppingContent_AccountShippingShippingService extends Google_Model
{
  public $active;
  protected $calculationMethodType = 'Google_Service_ShoppingContent_AccountShippingShippingServiceCalculationMethod';
  protected $calculationMethodDataType = '';
  protected $costRuleTreeType = 'Google_Service_ShoppingContent_AccountShippingShippingServiceCostRule';
  protected $costRuleTreeDataType = '';
  public $maxDaysInTransit;
  public $minDaysInTransit;
  public $name;
  public $saleCountry;

  public function setActive($active)
  {
    $this->active = $active;
  }
  public function getActive()
  {
    return $this->active;
  }
  public function setCalculationMethod(Google_Service_ShoppingContent_AccountShippingShippingServiceCalculationMethod $calculationMethod)
  {
    $this->calculationMethod = $calculationMethod;
  }
  public function getCalculationMethod()
  {
    return $this->calculationMethod;
  }
  public function setCostRuleTree(Google_Service_ShoppingContent_AccountShippingShippingServiceCostRule $costRuleTree)
  {
    $this->costRuleTree = $costRuleTree;
  }
  public function getCostRuleTree()
  {
    return $this->costRuleTree;
  }
  public function setMaxDaysInTransit($maxDaysInTransit)
  {
    $this->maxDaysInTransit = $maxDaysInTransit;
  }
  public function getMaxDaysInTransit()
  {
    return $this->maxDaysInTransit;
  }
  public function setMinDaysInTransit($minDaysInTransit)
  {
    $this->minDaysInTransit = $minDaysInTransit;
  }
  public function getMinDaysInTransit()
  {
    return $this->minDaysInTransit;
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
}
