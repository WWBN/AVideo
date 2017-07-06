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

class Google_Service_Dfareporting_ReportCompatibleFields extends Google_Collection
{
  protected $collection_key = 'pivotedActivityMetrics';
  protected $dimensionFiltersType = 'Google_Service_Dfareporting_Dimension';
  protected $dimensionFiltersDataType = 'array';
  protected $dimensionsType = 'Google_Service_Dfareporting_Dimension';
  protected $dimensionsDataType = 'array';
  public $kind;
  protected $metricsType = 'Google_Service_Dfareporting_Metric';
  protected $metricsDataType = 'array';
  protected $pivotedActivityMetricsType = 'Google_Service_Dfareporting_Metric';
  protected $pivotedActivityMetricsDataType = 'array';

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
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setMetrics($metrics)
  {
    $this->metrics = $metrics;
  }
  public function getMetrics()
  {
    return $this->metrics;
  }
  public function setPivotedActivityMetrics($pivotedActivityMetrics)
  {
    $this->pivotedActivityMetrics = $pivotedActivityMetrics;
  }
  public function getPivotedActivityMetrics()
  {
    return $this->pivotedActivityMetrics;
  }
}
