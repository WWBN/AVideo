<?php
/*
 * Copyright 2014 Google Inc.
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

namespace Google\Service\SA360;

class GoogleAdsSearchads360V0CommonSegments extends \Google\Model
{
  /**
   * @var string
   */
  public $conversionAction;
  /**
   * @var string
   */
  public $conversionActionCategory;
  /**
   * @var string
   */
  public $conversionActionName;
  /**
   * @var string
   */
  public $date;
  /**
   * @var string
   */
  public $dayOfWeek;
  /**
   * @var string
   */
  public $device;
  /**
   * @var string
   */
  public $month;
  /**
   * @var string
   */
  public $quarter;
  /**
   * @var string
   */
  public $week;
  /**
   * @var int
   */
  public $year;

  /**
   * @param string
   */
  public function setConversionAction($conversionAction)
  {
    $this->conversionAction = $conversionAction;
  }
  /**
   * @return string
   */
  public function getConversionAction()
  {
    return $this->conversionAction;
  }
  /**
   * @param string
   */
  public function setConversionActionCategory($conversionActionCategory)
  {
    $this->conversionActionCategory = $conversionActionCategory;
  }
  /**
   * @return string
   */
  public function getConversionActionCategory()
  {
    return $this->conversionActionCategory;
  }
  /**
   * @param string
   */
  public function setConversionActionName($conversionActionName)
  {
    $this->conversionActionName = $conversionActionName;
  }
  /**
   * @return string
   */
  public function getConversionActionName()
  {
    return $this->conversionActionName;
  }
  /**
   * @param string
   */
  public function setDate($date)
  {
    $this->date = $date;
  }
  /**
   * @return string
   */
  public function getDate()
  {
    return $this->date;
  }
  /**
   * @param string
   */
  public function setDayOfWeek($dayOfWeek)
  {
    $this->dayOfWeek = $dayOfWeek;
  }
  /**
   * @return string
   */
  public function getDayOfWeek()
  {
    return $this->dayOfWeek;
  }
  /**
   * @param string
   */
  public function setDevice($device)
  {
    $this->device = $device;
  }
  /**
   * @return string
   */
  public function getDevice()
  {
    return $this->device;
  }
  /**
   * @param string
   */
  public function setMonth($month)
  {
    $this->month = $month;
  }
  /**
   * @return string
   */
  public function getMonth()
  {
    return $this->month;
  }
  /**
   * @param string
   */
  public function setQuarter($quarter)
  {
    $this->quarter = $quarter;
  }
  /**
   * @return string
   */
  public function getQuarter()
  {
    return $this->quarter;
  }
  /**
   * @param string
   */
  public function setWeek($week)
  {
    $this->week = $week;
  }
  /**
   * @return string
   */
  public function getWeek()
  {
    return $this->week;
  }
  /**
   * @param int
   */
  public function setYear($year)
  {
    $this->year = $year;
  }
  /**
   * @return int
   */
  public function getYear()
  {
    return $this->year;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleAdsSearchads360V0CommonSegments::class, 'Google_Service_SA360_GoogleAdsSearchads360V0CommonSegments');
