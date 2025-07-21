<?php
/*
 * Copyright 2014 Google Inc.
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

namespace Google\Service\OSConfig;

class GoogleCloudOsconfigV2PolicyOrchestratorIterationState extends \Google\Model
{
  protected $errorType = Status::class;
  protected $errorDataType = '';
  /**
   * @var string
   */
  public $failedActions;
  /**
   * @var string
   */
  public $finishTime;
  /**
   * @var string
   */
  public $iterationId;
  /**
   * @var string
   */
  public $performedActions;
  /**
   * @var float
   */
  public $progress;
  /**
   * @var string
   */
  public $startTime;
  /**
   * @var string
   */
  public $state;

  /**
   * @param Status
   */
  public function setError(Status $error)
  {
    $this->error = $error;
  }
  /**
   * @return Status
   */
  public function getError()
  {
    return $this->error;
  }
  /**
   * @param string
   */
  public function setFailedActions($failedActions)
  {
    $this->failedActions = $failedActions;
  }
  /**
   * @return string
   */
  public function getFailedActions()
  {
    return $this->failedActions;
  }
  /**
   * @param string
   */
  public function setFinishTime($finishTime)
  {
    $this->finishTime = $finishTime;
  }
  /**
   * @return string
   */
  public function getFinishTime()
  {
    return $this->finishTime;
  }
  /**
   * @param string
   */
  public function setIterationId($iterationId)
  {
    $this->iterationId = $iterationId;
  }
  /**
   * @return string
   */
  public function getIterationId()
  {
    return $this->iterationId;
  }
  /**
   * @param string
   */
  public function setPerformedActions($performedActions)
  {
    $this->performedActions = $performedActions;
  }
  /**
   * @return string
   */
  public function getPerformedActions()
  {
    return $this->performedActions;
  }
  /**
   * @param float
   */
  public function setProgress($progress)
  {
    $this->progress = $progress;
  }
  /**
   * @return float
   */
  public function getProgress()
  {
    return $this->progress;
  }
  /**
   * @param string
   */
  public function setStartTime($startTime)
  {
    $this->startTime = $startTime;
  }
  /**
   * @return string
   */
  public function getStartTime()
  {
    return $this->startTime;
  }
  /**
   * @param string
   */
  public function setState($state)
  {
    $this->state = $state;
  }
  /**
   * @return string
   */
  public function getState()
  {
    return $this->state;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudOsconfigV2PolicyOrchestratorIterationState::class, 'Google_Service_OSConfig_GoogleCloudOsconfigV2PolicyOrchestratorIterationState');
