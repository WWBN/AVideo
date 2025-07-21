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

class GoogleCloudApihubV1Version extends \Google\Collection
{
  protected $collection_key = 'specs';
  protected $accreditationType = GoogleCloudApihubV1AttributeValues::class;
  protected $accreditationDataType = '';
  /**
   * @var string[]
   */
  public $apiOperations;
  protected $attributesType = GoogleCloudApihubV1AttributeValues::class;
  protected $attributesDataType = 'map';
  protected $complianceType = GoogleCloudApihubV1AttributeValues::class;
  protected $complianceDataType = '';
  /**
   * @var string
   */
  public $createTime;
  /**
   * @var string[]
   */
  public $definitions;
  /**
   * @var string[]
   */
  public $deployments;
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
  protected $lifecycleType = GoogleCloudApihubV1AttributeValues::class;
  protected $lifecycleDataType = '';
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $selectedDeployment;
  protected $sourceMetadataType = GoogleCloudApihubV1SourceMetadata::class;
  protected $sourceMetadataDataType = 'array';
  /**
   * @var string[]
   */
  public $specs;
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setAccreditation(GoogleCloudApihubV1AttributeValues $accreditation)
  {
    $this->accreditation = $accreditation;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getAccreditation()
  {
    return $this->accreditation;
  }
  /**
   * @param string[]
   */
  public function setApiOperations($apiOperations)
  {
    $this->apiOperations = $apiOperations;
  }
  /**
   * @return string[]
   */
  public function getApiOperations()
  {
    return $this->apiOperations;
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
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setCompliance(GoogleCloudApihubV1AttributeValues $compliance)
  {
    $this->compliance = $compliance;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getCompliance()
  {
    return $this->compliance;
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
   * @param string[]
   */
  public function setDefinitions($definitions)
  {
    $this->definitions = $definitions;
  }
  /**
   * @return string[]
   */
  public function getDefinitions()
  {
    return $this->definitions;
  }
  /**
   * @param string[]
   */
  public function setDeployments($deployments)
  {
    $this->deployments = $deployments;
  }
  /**
   * @return string[]
   */
  public function getDeployments()
  {
    return $this->deployments;
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
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setLifecycle(GoogleCloudApihubV1AttributeValues $lifecycle)
  {
    $this->lifecycle = $lifecycle;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getLifecycle()
  {
    return $this->lifecycle;
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
  public function setSelectedDeployment($selectedDeployment)
  {
    $this->selectedDeployment = $selectedDeployment;
  }
  /**
   * @return string
   */
  public function getSelectedDeployment()
  {
    return $this->selectedDeployment;
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
   * @param string[]
   */
  public function setSpecs($specs)
  {
    $this->specs = $specs;
  }
  /**
   * @return string[]
   */
  public function getSpecs()
  {
    return $this->specs;
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
class_alias(GoogleCloudApihubV1Version::class, 'Google_Service_APIhub_GoogleCloudApihubV1Version');
