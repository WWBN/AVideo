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

class Google_Service_Sheets_ChartSpec extends Google_Model
{
  protected $basicChartType = 'Google_Service_Sheets_BasicChartSpec';
  protected $basicChartDataType = '';
  public $hiddenDimensionStrategy;
  protected $pieChartType = 'Google_Service_Sheets_PieChartSpec';
  protected $pieChartDataType = '';
  public $title;

  public function setBasicChart(Google_Service_Sheets_BasicChartSpec $basicChart)
  {
    $this->basicChart = $basicChart;
  }
  public function getBasicChart()
  {
    return $this->basicChart;
  }
  public function setHiddenDimensionStrategy($hiddenDimensionStrategy)
  {
    $this->hiddenDimensionStrategy = $hiddenDimensionStrategy;
  }
  public function getHiddenDimensionStrategy()
  {
    return $this->hiddenDimensionStrategy;
  }
  public function setPieChart(Google_Service_Sheets_PieChartSpec $pieChart)
  {
    $this->pieChart = $pieChart;
  }
  public function getPieChart()
  {
    return $this->pieChart;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}
