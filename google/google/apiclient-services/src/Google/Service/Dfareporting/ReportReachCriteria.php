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

class Google_Service_Dfareporting_ReportReachCriteria extends Google_Collection
{
  protected $collection_key = 'reachByFrequencyMetricNames';
  protected $activitiesType = 'Google_Service_Dfareporting_Activities';
  protected $activitiesDataType = '';
  protected $customRichMediaEventsType = 'Google_Service_Dfareporting_CustomRichMediaEvents';
  protected $customRichMediaEventsDataType = '';
  protected $dateRangeType = 'Google_Service_Dfareporting_DateRange';
  protected $dateRangeDataType = '';
  protected $dimensionFiltersType = 'Google_Service_Dfareporting_DimensionValue';
  protected $dimensionFiltersDataType = 'array';
  protected $dimensionsType = 'Google_Service_Dfareporting_SortedDimension';
  protected $dimensionsDataType = 'array';
  public $enableAllDimensionCombinations;
  public $metricNames;
  public $reachByFrequencyMetricNames;

  public function setActivities(Google_Service_Dfareporting_Activities $activities)
  {
    $this->activities = $activities;
  }
  public function getActivities()
  {
    return $this->activities;
  }
  public function setCustomRichMediaEvents(Google_Service_Dfareporting_CustomRichMediaEvents $customRichMediaEvents)
  {
    $this->customRichMediaEvents = $customRichMediaEvents;
  }
  public function getCustomRichMediaEvents()
  {
    return $this->customRichMediaEvents;
  }
  public function setDateRange(Google_Service_Dfareporting_DateRange $dateRange)
  {
    $this->dateRange = $dateRange;
  }
  public function getDateRange()
  {
    return $this->dateRange;
  }
  public function setDimensionFilters($dimensionFilters)
  {
    $this->dimensionFilters = $dimensionFilters;
  }
  public function getDimensionFilters()
  {
    return $this->dimensionFilters;
  }
  public function setDimensions($dimensions)
  {
    $this->dimensions = $dimensions;
  }
  public function getDimensions()
  {
    return $this->dimensions;
  }
  public function setEnableAllDimensionCombinations($enableAllDimensionCombinations)
  {
    $this->enableAllDimensionCombinations = $enableAllDimensionCombinations;
  }
  public function getEnableAllDimensionCombinations()
  {
    return $this->enableAllDimensionCombinations;
  }
  public function setMetricNames($metricNames)
  {
    $this->metricNames = $metricNames;
  }
  public function getMetricNames()
  {
    return $this->metricNames;
  }
  public function setReachByFrequencyMetricNames($reachByFrequencyMetricNames)
  {
    $this->reachByFrequencyMetricNames = $reachByFrequencyMetricNames;
  }
  public function getReachByFrequencyMetricNames()
  {
    return $this->reachByFrequencyMetricNames;
  }
}
