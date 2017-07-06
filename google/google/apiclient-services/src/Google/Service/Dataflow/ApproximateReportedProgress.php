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

class Google_Service_Dataflow_ApproximateReportedProgress extends Google_Model
{
  protected $consumedParallelismType = 'Google_Service_Dataflow_ReportedParallelism';
  protected $consumedParallelismDataType = '';
  public $fractionConsumed;
  protected $positionType = 'Google_Service_Dataflow_Position';
  protected $positionDataType = '';
  protected $remainingParallelismType = 'Google_Service_Dataflow_ReportedParallelism';
  protected $remainingParallelismDataType = '';

  public function setConsumedParallelism(Google_Service_Dataflow_ReportedParallelism $consumedParallelism)
  {
    $this->consumedParallelism = $consumedParallelism;
  }
  public function getConsumedParallelism()
  {
    return $this->consumedParallelism;
  }
  public function setFractionConsumed($fractionConsumed)
  {
    $this->fractionConsumed = $fractionConsumed;
  }
  public function getFractionConsumed()
  {
    return $this->fractionConsumed;
  }
  public function setPosition(Google_Service_Dataflow_Position $position)
  {
    $this->position = $position;
  }
  public function getPosition()
  {
    return $this->position;
  }
  public function setRemainingParallelism(Google_Service_Dataflow_ReportedParallelism $remainingParallelism)
  {
    $this->remainingParallelism = $remainingParallelism;
  }
  public function getRemainingParallelism()
  {
    return $this->remainingParallelism;
  }
}
