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

class Google_Service_Dfareporting_ReportPathToConversionCriteria extends Google_Collection
{
  protected $collection_key = 'perInteractionDimensions';
  protected $activityFiltersType = 'Google_Service_Dfareporting_DimensionValue';
  protected $activityFiltersDataType = 'array';
  protected $conversionDimensionsType = 'Google_Service_Dfareporting_SortedDimension';
  protected $conversionDimensionsDataType = 'array';
  protected $customFloodlightVariablesType = 'Google_Service_Dfareporting_SortedDimension';
  protected $customFloodlightVariablesDataType = 'array';
  protected $customRichMediaEventsType = 'Google_Service_Dfareporting_DimensionValue';
  protected $customRichMediaEventsDataType = 'array';
  protected $dateRangeType = 'Google_Service_Dfareporting_DateRange';
  protected $dateRangeDataType = '';
  protected $floodlightConfigIdType = 'Google_Service_Dfareporting_DimensionValue';
  protected $floodlightConfigIdDataType = '';
  public $metricNames;
  protected $perInteractionDimensionsType = 'Google_Service_Dfareporting_SortedDimension';
  protected $perInteractionDimensionsDataType = 'array';
  protected $reportPropertiesType = 'Google_Service_Dfareporting_ReportPathToConversionCriteriaReportProperties';
  protected $reportPropertiesDataType = '';

  public function setActivityFilters($activityFilters)
  {
    $this->activityFilters = $activityFilters;
  }
  public function getActivityFilters()
  {
    return $this->activityFilters;
  }
  public function setConversionDimensions($conversionDimensions)
  {
    $this->conversionDimensions = $conversionDimensions;
  }
  public function getConversionDimensions()
  {
    return $this->conversionDimensions;
  }
  public function setCustomFloodlightVariables($customFloodlightVariables)
  {
    $this->customFloodlightVariables = $customFloodlightVariables;
  }
  public function getCustomFloodlightVariables()
  {
    return $this->customFloodlightVariables;
  }
  public function setCustomRichMediaEvents($customRichMediaEvents)
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
  public function setFloodlightConfigId(Google_Service_Dfareporting_DimensionValue $floodlightConfigId)
  {
    $this->floodlightConfigId = $floodlightConfigId;
  }
  public function getFloodlightConfigId()
  {
    return $this->floodlightConfigId;
  }
  public function setMetricNames($metricNames)
  {
    $this->metricNames = $metricNames;
  }
  public function getMetricNames()
  {
    return $this->metricNames;
  }
  public function setPerInteractionDimensions($perInteractionDimensions)
  {
    $this->perInteractionDimensions = $perInteractionDimensions;
  }
  public function getPerInteractionDimensions()
  {
    return $this->perInteractionDimensions;
  }
  public function setReportProperties(Google_Service_Dfareporting_ReportPathToConversionCriteriaReportProperties $reportProperties)
  {
    $this->reportProperties = $reportProperties;
  }
  public function getReportProperties()
  {
    return $this->reportProperties;
  }
}
