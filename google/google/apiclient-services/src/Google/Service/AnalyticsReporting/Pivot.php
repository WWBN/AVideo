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

class Google_Service_AnalyticsReporting_Pivot extends Google_Collection
{
  protected $collection_key = 'metrics';
  protected $dimensionFilterClausesType = 'Google_Service_AnalyticsReporting_DimensionFilterClause';
  protected $dimensionFilterClausesDataType = 'array';
  protected $dimensionsType = 'Google_Service_AnalyticsReporting_Dimension';
  protected $dimensionsDataType = 'array';
  public $maxGroupCount;
  protected $metricsType = 'Google_Service_AnalyticsReporting_Metric';
  protected $metricsDataType = 'array';
  public $startGroup;

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
  public function setMaxGroupCount($maxGroupCount)
  {
    $this->maxGroupCount = $maxGroupCount;
  }
  public function getMaxGroupCount()
  {
    return $this->maxGroupCount;
  }
  public function setMetrics($metrics)
  {
    $this->metrics = $metrics;
  }
  public function getMetrics()
  {
    return $this->metrics;
  }
  public function setStartGroup($startGroup)
  {
    $this->startGroup = $startGroup;
  }
  public function getStartGroup()
  {
    return $this->startGroup;
  }
}
