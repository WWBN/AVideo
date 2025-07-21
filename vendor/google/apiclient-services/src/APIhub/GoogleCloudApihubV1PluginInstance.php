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

class GoogleCloudApihubV1PluginInstance extends \Google\Collection
{
  protected $collection_key = 'actions';
  protected $actionsType = GoogleCloudApihubV1PluginInstanceAction::class;
  protected $actionsDataType = 'array';
  protected $additionalConfigType = GoogleCloudApihubV1ConfigVariable::class;
  protected $additionalConfigDataType = 'map';
  protected $authConfigType = GoogleCloudApihubV1AuthConfig::class;
  protected $authConfigDataType = '';
  /**
   * @var string
   */
  public $createTime;
  /**
   * @var string
   */
  public $displayName;
  /**
   * @var string
   */
  public $errorMessage;
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $state;
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param GoogleCloudApihubV1PluginInstanceAction[]
   */
  public function setActions($actions)
  {
    $this->actions = $actions;
  }
  /**
   * @return GoogleCloudApihubV1PluginInstanceAction[]
   */
  public function getActions()
  {
    return $this->actions;
  }
  /**
   * @param GoogleCloudApihubV1ConfigVariable[]
   */
  public function setAdditionalConfig($additionalConfig)
  {
    $this->additionalConfig = $additionalConfig;
  }
  /**
   * @return GoogleCloudApihubV1ConfigVariable[]
   */
  public function getAdditionalConfig()
  {
    return $this->additionalConfig;
  }
  /**
   * @param GoogleCloudApihubV1AuthConfig
   */
  public function setAuthConfig(GoogleCloudApihubV1AuthConfig $authConfig)
  {
    $this->authConfig = $authConfig;
  }
  /**
   * @return GoogleCloudApihubV1AuthConfig
   */
  public function getAuthConfig()
  {
    return $this->authConfig;
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
   * @param string
   */
  public function setErrorMessage($errorMessage)
  {
    $this->errorMessage = $errorMessage;
  }
  /**
   * @return string
   */
  public function getErrorMessage()
  {
    return $this->errorMessage;
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
class_alias(GoogleCloudApihubV1PluginInstance::class, 'Google_Service_APIhub_GoogleCloudApihubV1PluginInstance');
