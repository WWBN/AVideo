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

class GoogleCloudOsconfigV2PolicyOrchestrator extends \Google\Model
{
  /**
   * @var string
   */
  public $action;
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
  public $etag;
  /**
   * @var string[]
   */
  public $labels;
  /**
   * @var string
   */
  public $name;
  protected $orchestratedResourceType = GoogleCloudOsconfigV2OrchestratedResource::class;
  protected $orchestratedResourceDataType = '';
  protected $orchestrationScopeType = GoogleCloudOsconfigV2OrchestrationScope::class;
  protected $orchestrationScopeDataType = '';
  protected $orchestrationStateType = GoogleCloudOsconfigV2PolicyOrchestratorOrchestrationState::class;
  protected $orchestrationStateDataType = '';
  /**
   * @var bool
   */
  public $reconciling;
  /**
   * @var string
   */
  public $state;
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param string
   */
  public function setAction($action)
  {
    $this->action = $action;
  }
  /**
   * @return string
   */
  public function getAction()
  {
    return $this->action;
  }
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
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  /**
   * @return string
   */
  public function getEtag()
  {
    return $this->etag;
  }
  /**
   * @param string[]
   */
  public function setLabels($labels)
  {
    $this->labels = $labels;
  }
  /**
   * @return string[]
   */
  public function getLabels()
  {
    return $this->labels;
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
   * @param GoogleCloudOsconfigV2OrchestratedResource
   */
  public function setOrchestratedResource(GoogleCloudOsconfigV2OrchestratedResource $orchestratedResource)
  {
    $this->orchestratedResource = $orchestratedResource;
  }
  /**
   * @return GoogleCloudOsconfigV2OrchestratedResource
   */
  public function getOrchestratedResource()
  {
    return $this->orchestratedResource;
  }
  /**
   * @param GoogleCloudOsconfigV2OrchestrationScope
   */
  public function setOrchestrationScope(GoogleCloudOsconfigV2OrchestrationScope $orchestrationScope)
  {
    $this->orchestrationScope = $orchestrationScope;
  }
  /**
   * @return GoogleCloudOsconfigV2OrchestrationScope
   */
  public function getOrchestrationScope()
  {
    return $this->orchestrationScope;
  }
  /**
   * @param GoogleCloudOsconfigV2PolicyOrchestratorOrchestrationState
   */
  public function setOrchestrationState(GoogleCloudOsconfigV2PolicyOrchestratorOrchestrationState $orchestrationState)
  {
    $this->orchestrationState = $orchestrationState;
  }
  /**
   * @return GoogleCloudOsconfigV2PolicyOrchestratorOrchestrationState
   */
  public function getOrchestrationState()
  {
    return $this->orchestrationState;
  }
  /**
   * @param bool
   */
  public function setReconciling($reconciling)
  {
    $this->reconciling = $reconciling;
  }
  /**
   * @return bool
   */
  public function getReconciling()
  {
    return $this->reconciling;
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
class_alias(GoogleCloudOsconfigV2PolicyOrchestrator::class, 'Google_Service_OSConfig_GoogleCloudOsconfigV2PolicyOrchestrator');
