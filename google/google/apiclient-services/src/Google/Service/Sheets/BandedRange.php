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

class Google_Service_Sheets_BandedRange extends Google_Model
{
  public $bandedRangeId;
  protected $columnPropertiesType = 'Google_Service_Sheets_BandingProperties';
  protected $columnPropertiesDataType = '';
  protected $rangeType = 'Google_Service_Sheets_GridRange';
  protected $rangeDataType = '';
  protected $rowPropertiesType = 'Google_Service_Sheets_BandingProperties';
  protected $rowPropertiesDataType = '';

  public function setBandedRangeId($bandedRangeId)
  {
    $this->bandedRangeId = $bandedRangeId;
  }
  public function getBandedRangeId()
  {
    return $this->bandedRangeId;
  }
  public function setColumnProperties(Google_Service_Sheets_BandingProperties $columnProperties)
  {
    $this->columnProperties = $columnProperties;
  }
  public function getColumnProperties()
  {
    return $this->columnProperties;
  }
  public function setRange(Google_Service_Sheets_GridRange $range)
  {
    $this->range = $range;
  }
  public function getRange()
  {
    return $this->range;
  }
  public function setRowProperties(Google_Service_Sheets_BandingProperties $rowProperties)
  {
    $this->rowProperties = $rowProperties;
  }
  public function getRowProperties()
  {
    return $this->rowProperties;
  }
}
