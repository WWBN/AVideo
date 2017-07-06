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

class Google_Service_AnalyticsReporting_ReportRequest extends Google_Collection
{
  protected $collection_key = 'segments';
  protected $cohortGroupType = 'Google_Service_AnalyticsReporting_CohortGroup';
  protected $cohortGroupDataType = '';
  protected $dateRangesType = 'Google_Service_AnalyticsReporting_DateRange';
  protected $dateRangesDataType = 'array';
  protected $dimensionFilterClausesType = 'Google_Service_AnalyticsReporting_DimensionFilterClause';
  protected $dimensionFilterClausesDataType = 'array';
  protected $dimensionsType = 'Google_Service_AnalyticsReporting_Dimension';
  protected $dimensionsDataType = 'array';
  public $filtersExpression;
  public $hideTotals;
  public $hideValueRanges;
  public $includeEmptyRows;
  protected $metricFilterClausesType = 'Google_Service_AnalyticsReporting_MetricFilterClause';
  protected $metricFilterClausesDataType = 'array';
  protected $metricsType = 'Google_Service_AnalyticsReporting_Metric';
  protected $metricsDataType = 'array';
  protected $orderBysType = 'Google_Service_AnalyticsReporting_OrderBy';
  protected $orderBysDataType = 'array';
  public $pageSize;
  public $pageToken;
  protected $pivotsType = 'Google_Service_AnalyticsReporting_Pivot';
  protected $pivotsDataType = 'array';
  public $samplingLevel;
  protected $segmentsType = 'Google_Service_AnalyticsReporting_Segment';
  protected $segmentsDataType = 'array';
  public $viewId;

  public function setCohortGroup(Google_Service_AnalyticsReporting_CohortGroup $cohortGroup)
  {
    $this->cohortGroup = $cohortGroup;
  }
  public function getCohortGroup()
  {
    return $this->cohortGroup;
  }
  public function setDateRanges($dateRanges)
  {
    $this->dateRanges = $dateRanges;
  }
  public function getDateRanges()
  {
    return $this->dateRanges;
  }
  public function setDimensionFilterClauses($dimensionFilterClauses)
  {
    $this->dimensionFilterClauses = $dimensionFilterClauses;
  }
  public function getDimensionFilterClauses()
  {
    return $this->dimensionFilterClauses;
  }
  public function setDimensions($dimensions)
  {
    $this->dimensions = $dimensions;
  }
  public function getDimensions()
  {
    return $this->dimensions;
  }
  public function setFiltersExpression($filtersExpression)
  {
    $this->filtersExpression = $filtersExpression;
  }
  public function getFiltersExpression()
  {
    return $this->filtersExpression;
  }
  public function setHideTotals($hideTotals)
  {
    $this->hideTotals = $hideTotals;
  }
  public function getHideTotals()
  {
    return $this->hideTotals;
  }
  public function setHideValueRanges($hideValueRanges)
  {
    $this->hideValueRanges = $hideValueRanges;
  }
  public function getHideValueRanges()
  {
    return $this->hideValueRanges;
  }
  public function setIncludeEmptyRows($includeEmptyRows)
  {
    $this->includeEmptyRows = $includeEmptyRows;
  }
  public function getIncludeEmptyRows()
  {
    return $this->includeEmptyRows;
  }
  public function setMetricFilterClauses($metricFilterClauses)
  {
    $this->metricFilterClauses = $metricFilterClauses;
  }
  public function getMetricFilterClauses()
  {
    return $this->metricFilterClauses;
  }
  public function setMetrics($metrics)
  {
    $this->metrics = $metrics;
  }
  public function getMetrics()
  {
    return $this->metrics;
  }
  public function setOrderBys($orderBys)
  {
    $this->orderBys = $orderBys;
  }
  public function getOrderBys()
  {
    return $this->orderBys;
  }
  public function setPageSize($pageSize)
  {
    $this->pageSize = $pageSize;
  }
  public function getPageSize()
  {
    return $this->pageSize;
  }
  public function setPageToken($pageToken)
  {
    $this->pageToken = $pageToken;
  }
  public function getPageToken()
  {
    return $this->pageToken;
  }
  public function setPivots($pivots)
  {
    $this->pivots = $pivots;
  }
  public function getPivots()
  {
    return $this->pivots;
  }
  public function setSamplingLevel($samplingLevel)
  {
    $this->samplingLevel = $samplingLevel;
  }
  public function getSamplingLevel()
  {
    return $this->samplingLevel;
  }
  public function setSegments($segments)
  {
    $this->segments = $segments;
  }
  public function getSegments()
  {
    return $this->segments;
  }
  public function setViewId($viewId)
  {
    $this->viewId = $viewId;
  }
  public function getViewId()
  {
    return $this->viewId;
  }
}
