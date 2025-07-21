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

class GoogleCloudApihubV1Curation extends \Google\Collection
{
  protected $collection_key = 'pluginInstanceActions';
  /**
   * @var string
   */
  public $createTime;
  /**
   * @var string
   */
  public $description;
  /**
   * @var string
   */
  public $displayName;
  protected $endpointType = GoogleCloudApihubV1Endpoint::class;
  protected $endpointDataType = '';
  /**
   * @var string
   */
  public $lastExecutionErrorCode;
  /**
   * @var string
   */
  public $lastExecutionErrorMessage;
  /**
   * @var string
   */
  public $lastExecutionState;
  /**
   * @var string
   */
  public $name;
  protected $pluginInstanceActionsType = GoogleCloudApihubV1PluginInstanceActionID::class;
  protected $pluginInstanceActionsDataType = 'array';
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param string
   */
  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  /**
   * @return string
   */
  public function getCreateTime()
  {
    return $this->createTime;
  }
  /**
   * @param string
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }
  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }
  /**
   * @param string
   */
  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  /**
   * @return string
   */
  public function getDisplayName()
  {
    return $this->displayName;
  }
  /**
   * @param GoogleCloudApihubV1Endpoint
   */
  public function setEndpoint(GoogleCloudApihubV1Endpoint $endpoint)
  {
    $this->endpoint = $endpoint;
  }
  /**
   * @return GoogleCloudApihubV1Endpoint
   */
  public function getEndpoint()
  {
    return $this->endpoint;
  }
  /**
   * @param string
   */
  public function setLastExecutionErrorCode($lastExecutionErrorCode)
  {
    $this->lastExecutionErrorCode = $lastExecutionErrorCode;
  }
  /**
   * @return string
   */
  public function getLastExecutionErrorCode()
  {
    return $this->lastExecutionErrorCode;
  }
  /**
   * @param string
   */
  public function setLastExecutionErrorMessage($lastExecutionErrorMessage)
  {
    $this->lastExecutionErrorMessage = $lastExecutionErrorMessage;
  }
  /**
   * @return string
   */
  public function getLastExecutionErrorMessage()
  {
    return $this->lastExecutionErrorMessage;
  }
  /**
   * @param string
   */
  public function setLastExecutionState($lastExecutionState)
  {
    $this->lastExecutionState = $lastExecutionState;
  }
  /**
   * @return string
   */
  public function getLastExecutionState()
  {
    return $this->lastExecutionState;
  }
  /**
   * @param string
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param GoogleCloudApihubV1PluginInstanceActionID[]
   */
  public function setPluginInstanceActions($pluginInstanceActions)
  {
    $this->pluginInstanceActions = $pluginInstanceActions;
  }
  /**
   * @return GoogleCloudApihubV1PluginInstanceActionID[]
   */
  public function getPluginInstanceActions()
  {
    return $this->pluginInstanceActions;
  }
  /**
   * @param string
   */
  public function setUpdateTime($updateTime)
  {
    $this->updateTime = $updateTime;
  }
  /**
   * @return string
   */
  public function getUpdateTime()
  {
    return $this->updateTime;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1Curation::class, 'Google_Service_APIhub_GoogleCloudApihubV1Curation');
