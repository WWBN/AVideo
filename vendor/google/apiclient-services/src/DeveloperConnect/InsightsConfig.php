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

namespace Google\Service\DeveloperConnect;

class InsightsConfig extends \Google\Collection
{
  protected $collection_key = 'runtimeConfigs';
  /**
   * @var string[]
   */
  public $annotations;
  /**
   * @var string
   */
  public $appHubApplication;
  protected $artifactConfigsType = ArtifactConfig::class;
  protected $artifactConfigsDataType = 'array';
  /**
   * @var string
   */
  public $createTime;
  protected $errorsType = Status::class;
  protected $errorsDataType = 'array';
  /**
   * @var string[]
   */
  public $labels;
  /**
   * @var string
   */
  public $name;
  /**
   * @var bool
   */
  public $reconciling;
  protected $runtimeConfigsType = RuntimeConfig::class;
  protected $runtimeConfigsDataType = 'array';
  /**
   * @var string
   */
  public $state;
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param string[]
   */
  public function setAnnotations($annotations)
  {
    $this->annotations = $annotations;
  }
  /**
   * @return string[]
   */
  public function getAnnotations()
  {
    return $this->annotations;
  }
  /**
   * @param string
   */
  public function setAppHubApplication($appHubApplication)
  {
    $this->appHubApplication = $appHubApplication;
  }
  /**
   * @return string
   */
  public function getAppHubApplication()
  {
    return $this->appHubApplication;
  }
  /**
   * @param ArtifactConfig[]
   */
  public function setArtifactConfigs($artifactConfigs)
  {
    $this->artifactConfigs = $artifactConfigs;
  }
  /**
   * @return ArtifactConfig[]
   */
  public function getArtifactConfigs()
  {
    return $this->artifactConfigs;
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
   * @param Status[]
   */
  public function setErrors($errors)
  {
    $this->errors = $errors;
  }
  /**
   * @return Status[]
   */
  public function getErrors()
  {
    return $this->errors;
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
   * @param RuntimeConfig[]
   */
  public function setRuntimeConfigs($runtimeConfigs)
  {
    $this->runtimeConfigs = $runtimeConfigs;
  }
  /**
   * @return RuntimeConfig[]
   */
  public function getRuntimeConfigs()
  {
    return $this->runtimeConfigs;
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
class_alias(InsightsConfig::class, 'Google_Service_DeveloperConnect_InsightsConfig');
