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

namespace Google\Service\APIhub;

class GoogleCloudApihubV1PluginInstanceAction extends \Google\Model
{
  /**
   * @var string
   */
  public $actionId;
  protected $curationConfigType = GoogleCloudApihubV1CurationConfig::class;
  protected $curationConfigDataType = '';
  protected $hubInstanceActionType = GoogleCloudApihubV1ExecutionStatus::class;
  protected $hubInstanceActionDataType = '';
  /**
   * @var string
   */
  public $scheduleCronExpression;
  /**
   * @var string
   */
  public $scheduleTimeZone;
  /**
   * @var string
   */
  public $state;

  /**
   * @param string
   */
  public function setActionId($actionId)
  {
    $this->actionId = $actionId;
  }
  /**
   * @return string
   */
  public function getActionId()
  {
    return $this->actionId;
  }
  /**
   * @param GoogleCloudApihubV1CurationConfig
   */
  public function setCurationConfig(GoogleCloudApihubV1CurationConfig $curationConfig)
  {
    $this->curationConfig = $curationConfig;
  }
  /**
   * @return GoogleCloudApihubV1CurationConfig
   */
  public function getCurationConfig()
  {
    return $this->curationConfig;
  }
  /**
   * @param GoogleCloudApihubV1ExecutionStatus
   */
  public function setHubInstanceAction(GoogleCloudApihubV1ExecutionStatus $hubInstanceAction)
  {
    $this->hubInstanceAction = $hubInstanceAction;
  }
  /**
   * @return GoogleCloudApihubV1ExecutionStatus
   */
  public function getHubInstanceAction()
  {
    return $this->hubInstanceAction;
  }
  /**
   * @param string
   */
  public function setScheduleCronExpression($scheduleCronExpression)
  {
    $this->scheduleCronExpression = $scheduleCronExpression;
  }
  /**
   * @return string
   */
  public function getScheduleCronExpression()
  {
    return $this->scheduleCronExpression;
  }
  /**
   * @param string
   */
  public function setScheduleTimeZone($scheduleTimeZone)
  {
    $this->scheduleTimeZone = $scheduleTimeZone;
  }
  /**
   * @return string
   */
  public function getScheduleTimeZone()
  {
    return $this->scheduleTimeZone;
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
class_alias(GoogleCloudApihubV1PluginInstanceAction::class, 'Google_Service_APIhub_GoogleCloudApihubV1PluginInstanceAction');
