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

class Google_Service_Sheets_Sheet extends Google_Collection
{
  protected $collection_key = 'protectedRanges';
  protected $bandedRangesType = 'Google_Service_Sheets_BandedRange';
  protected $bandedRangesDataType = 'array';
  protected $basicFilterType = 'Google_Service_Sheets_BasicFilter';
  protected $basicFilterDataType = '';
  protected $chartsType = 'Google_Service_Sheets_EmbeddedChart';
  protected $chartsDataType = 'array';
  protected $conditionalFormatsType = 'Google_Service_Sheets_ConditionalFormatRule';
  protected $conditionalFormatsDataType = 'array';
  protected $dataType = 'Google_Service_Sheets_GridData';
  protected $dataDataType = 'array';
  protected $filterViewsType = 'Google_Service_Sheets_FilterView';
  protected $filterViewsDataType = 'array';
  protected $mergesType = 'Google_Service_Sheets_GridRange';
  protected $mergesDataType = 'array';
  protected $propertiesType = 'Google_Service_Sheets_SheetProperties';
  protected $propertiesDataType = '';
  protected $protectedRangesType = 'Google_Service_Sheets_ProtectedRange';
  protected $protectedRangesDataType = 'array';

  public function setBandedRanges($bandedRanges)
  {
    $this->bandedRanges = $bandedRanges;
  }
  public function getBandedRanges()
  {
    return $this->bandedRanges;
  }
  public function setBasicFilter(Google_Service_Sheets_BasicFilter $basicFilter)
  {
    $this->basicFilter = $basicFilter;
  }
  public function getBasicFilter()
  {
    return $this->basicFilter;
  }
  public function setCharts($charts)
  {
    $this->charts = $charts;
  }
  public function getCharts()
  {
    return $this->charts;
  }
  public function setConditionalFormats($conditionalFormats)
  {
    $this->conditionalFormats = $conditionalFormats;
  }
  public function getConditionalFormats()
  {
    return $this->conditionalFormats;
  }
  public function setData($data)
  {
    $this->data = $data;
  }
  public function getData()
  {
    return $this->data;
  }
  public function setFilterViews($filterViews)
  {
    $this->filterViews = $filterViews;
  }
  public function getFilterViews()
  {
    return $this->filterViews;
  }
  public function setMerges($merges)
  {
    $this->merges = $merges;
  }
  public function getMerges()
  {
    return $this->merges;
  }
  public function setProperties(Google_Service_Sheets_SheetProperties $properties)
  {
    $this->properties = $properties;
  }
  public function getProperties()
  {
    return $this->properties;
  }
  public function setProtectedRanges($protectedRanges)
  {
    $this->protectedRanges = $protectedRanges;
  }
  public function getProtectedRanges()
  {
    return $this->protectedRanges;
  }
}
