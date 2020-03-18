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

class Google_Service_YouTubeAnalytics_BatchReport extends Google_Collection
{
  protected $collection_key = 'outputs';
  public $id;
  public $kind;
  protected $outputsType = 'Google_Service_YouTubeAnalytics_BatchReportOutputs';
  protected $outputsDataType = 'array';
  public $reportId;
  protected $timeSpanType = 'Google_Service_YouTubeAnalytics_BatchReportTimeSpan';
  protected $timeSpanDataType = '';
  public $timeUpdated;

  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setOutputs($outputs)
  {
    $this->outputs = $outputs;
  }
  public function getOutputs()
  {
    return $this->outputs;
  }
  public function setReportId($reportId)
  {
    $this->reportId = $reportId;
  }
  public function getReportId()
  {
    return $this->reportId;
  }
  public function setTimeSpan(Google_Service_YouTubeAnalytics_BatchReportTimeSpan $timeSpan)
  {
    $this->timeSpan = $timeSpan;
  }
  public function getTimeSpan()
  {
    return $this->timeSpan;
  }
  public function setTimeUpdated($timeUpdated)
  {
    $this->timeUpdated = $timeUpdated;
  }
  public function getTimeUpdated()
  {
    return $this->timeUpdated;
  }
}
