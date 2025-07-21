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

class GoogleCloudApihubV1Plugin extends \Google\Collection
{
  protected $collection_key = 'actionsConfig';
  protected $actionsConfigType = GoogleCloudApihubV1PluginActionConfig::class;
  protected $actionsConfigDataType = 'array';
  protected $configTemplateType = GoogleCloudApihubV1ConfigTemplate::class;
  protected $configTemplateDataType = '';
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
  protected $documentationType = GoogleCloudApihubV1Documentation::class;
  protected $documentationDataType = '';
  protected $hostingServiceType = GoogleCloudApihubV1HostingService::class;
  protected $hostingServiceDataType = '';
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $ownershipType;
  /**
   * @var string
   */
  public $pluginCategory;
  /**
   * @var string
   */
  public $state;
  protected $typeType = GoogleCloudApihubV1AttributeValues::class;
  protected $typeDataType = '';
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param GoogleCloudApihubV1PluginActionConfig[]
   */
  public function setActionsConfig($actionsConfig)
  {
    $this->actionsConfig = $actionsConfig;
  }
  /**
   * @return GoogleCloudApihubV1PluginActionConfig[]
   */
  public function getActionsConfig()
  {
    return $this->actionsConfig;
  }
  /**
   * @param GoogleCloudApihubV1ConfigTemplate
   */
  public function setConfigTemplate(GoogleCloudApihubV1ConfigTemplate $configTemplate)
  {
    $this->configTemplate = $configTemplate;
  }
  /**
   * @return GoogleCloudApihubV1ConfigTemplate
   */
  public function getConfigTemplate()
  {
    return $this->configTemplate;
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
   * @param GoogleCloudApihubV1Documentation
   */
  public function setDocumentation(GoogleCloudApihubV1Documentation $documentation)
  {
    $this->documentation = $documentation;
  }
  /**
   * @return GoogleCloudApihubV1Documentation
   */
  public function getDocumentation()
  {
    return $this->documentation;
  }
  /**
   * @param GoogleCloudApihubV1HostingService
   */
  public function setHostingService(GoogleCloudApihubV1HostingService $hostingService)
  {
    $this->hostingService = $hostingService;
  }
  /**
   * @return GoogleCloudApihubV1HostingService
   */
  public function getHostingService()
  {
    return $this->hostingService;
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
  public function setOwnershipType($ownershipType)
  {
    $this->ownershipType = $ownershipType;
  }
  /**
   * @return string
   */
  public function getOwnershipType()
  {
    return $this->ownershipType;
  }
  /**
   * @param string
   */
  public function setPluginCategory($pluginCategory)
  {
    $this->pluginCategory = $pluginCategory;
  }
  /**
   * @return string
   */
  public function getPluginCategory()
  {
    return $this->pluginCategory;
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
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setType(GoogleCloudApihubV1AttributeValues $type)
  {
    $this->type = $type;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getType()
  {
    return $this->type;
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
class_alias(GoogleCloudApihubV1Plugin::class, 'Google_Service_APIhub_GoogleCloudApihubV1Plugin');
