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

class Google_Service_Sheets_BasicChartSpec extends Google_Collection
{
  protected $collection_key = 'series';
  protected $axisType = 'Google_Service_Sheets_BasicChartAxis';
  protected $axisDataType = 'array';
  public $chartType;
  protected $domainsType = 'Google_Service_Sheets_BasicChartDomain';
  protected $domainsDataType = 'array';
  public $headerCount;
  public $legendPosition;
  protected $seriesType = 'Google_Service_Sheets_BasicChartSeries';
  protected $seriesDataType = 'array';

  public function setAxis($axis)
  {
    $this->axis = $axis;
  }
  public function getAxis()
  {
    return $this->axis;
  }
  public function setChartType($chartType)
  {
    $this->chartType = $chartType;
  }
  public function getChartType()
  {
    return $this->chartType;
  }
  public function setDomains($domains)
  {
    $this->domains = $domains;
  }
  public function getDomains()
  {
    return $this->domains;
  }
  public function setHeaderCount($headerCount)
  {
    $this->headerCount = $headerCount;
  }
  public function getHeaderCount()
  {
    return $this->headerCount;
  }
  public function setLegendPosition($legendPosition)
  {
    $this->legendPosition = $legendPosition;
  }
  public function getLegendPosition()
  {
    return $this->legendPosition;
  }
  public function setSeries($series)
  {
    $this->series = $series;
  }
  public function getSeries()
  {
    return $this->series;
  }
}
