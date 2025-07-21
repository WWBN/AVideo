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

class GoogleCloudApihubV1Deployment extends \Google\Collection
{
  protected $collection_key = 'sourceMetadata';
  /**
   * @var string[]
   */
  public $apiVersions;
  protected $attributesType = GoogleCloudApihubV1AttributeValues::class;
  protected $attributesDataType = 'map';
  /**
   * @var string
   */
  public $createTime;
  protected $deploymentTypeType = GoogleCloudApihubV1AttributeValues::class;
  protected $deploymentTypeDataType = '';
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
  /**
   * @var string[]
   */
  public $endpoints;
  protected $environmentType = GoogleCloudApihubV1AttributeValues::class;
  protected $environmentDataType = '';
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $resourceUri;
  protected $sloType = GoogleCloudApihubV1AttributeValues::class;
  protected $sloDataType = '';
  protected $sourceMetadataType = GoogleCloudApihubV1SourceMetadata::class;
  protected $sourceMetadataDataType = 'array';
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param string[]
   */
  public function setApiVersions($apiVersions)
  {
    $this->apiVersions = $apiVersions;
  }
  /**
   * @return string[]
   */
  public function getApiVersions()
  {
    return $this->apiVersions;
  }
  /**
   * @param GoogleCloudApihubV1AttributeValues[]
   */
  public function setAttributes($attributes)
  {
    $this->attributes = $attributes;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues[]
   */
  public function getAttributes()
  {
    return $this->attributes;
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
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setDeploymentType(GoogleCloudApihubV1AttributeValues $deploymentType)
  {
    $this->deploymentType = $deploymentType;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getDeploymentType()
  {
    return $this->deploymentType;
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
   * @param string[]
   */
  public function setEndpoints($endpoints)
  {
    $this->endpoints = $endpoints;
  }
  /**
   * @return string[]
   */
  public function getEndpoints()
  {
    return $this->endpoints;
  }
  /**
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setEnvironment(GoogleCloudApihubV1AttributeValues $environment)
  {
    $this->environment = $environment;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getEnvironment()
  {
    return $this->environment;
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
  public function setResourceUri($resourceUri)
  {
    $this->resourceUri = $resourceUri;
  }
  /**
   * @return string
   */
  public function getResourceUri()
  {
    return $this->resourceUri;
  }
  /**
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setSlo(GoogleCloudApihubV1AttributeValues $slo)
  {
    $this->slo = $slo;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getSlo()
  {
    return $this->slo;
  }
  /**
   * @param GoogleCloudApihubV1SourceMetadata[]
   */
  public function setSourceMetadata($sourceMetadata)
  {
    $this->sourceMetadata = $sourceMetadata;
  }
  /**
   * @return GoogleCloudApihubV1SourceMetadata[]
   */
  public function getSourceMetadata()
  {
    return $this->sourceMetadata;
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
class_alias(GoogleCloudApihubV1Deployment::class, 'Google_Service_APIhub_GoogleCloudApihubV1Deployment');
