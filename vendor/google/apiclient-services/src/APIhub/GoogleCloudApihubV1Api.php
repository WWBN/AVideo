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

class GoogleCloudApihubV1Api extends \Google\Collection
{
  protected $collection_key = 'versions';
  protected $apiFunctionalRequirementsType = GoogleCloudApihubV1AttributeValues::class;
  protected $apiFunctionalRequirementsDataType = '';
  protected $apiRequirementsType = GoogleCloudApihubV1AttributeValues::class;
  protected $apiRequirementsDataType = '';
  protected $apiStyleType = GoogleCloudApihubV1AttributeValues::class;
  protected $apiStyleDataType = '';
  protected $apiTechnicalRequirementsType = GoogleCloudApihubV1AttributeValues::class;
  protected $apiTechnicalRequirementsDataType = '';
  protected $attributesType = GoogleCloudApihubV1AttributeValues::class;
  protected $attributesDataType = 'map';
  protected $businessUnitType = GoogleCloudApihubV1AttributeValues::class;
  protected $businessUnitDataType = '';
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
  /**
   * @var string
   */
  public $fingerprint;
  protected $maturityLevelType = GoogleCloudApihubV1AttributeValues::class;
  protected $maturityLevelDataType = '';
  /**
   * @var string
   */
  public $name;
  protected $ownerType = GoogleCloudApihubV1Owner::class;
  protected $ownerDataType = '';
  /**
   * @var string
   */
  public $selectedVersion;
  protected $sourceMetadataType = GoogleCloudApihubV1SourceMetadata::class;
  protected $sourceMetadataDataType = 'array';
  protected $targetUserType = GoogleCloudApihubV1AttributeValues::class;
  protected $targetUserDataType = '';
  protected $teamType = GoogleCloudApihubV1AttributeValues::class;
  protected $teamDataType = '';
  /**
   * @var string
   */
  public $updateTime;
  /**
   * @var string[]
   */
  public $versions;

  /**
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setApiFunctionalRequirements(GoogleCloudApihubV1AttributeValues $apiFunctionalRequirements)
  {
    $this->apiFunctionalRequirements = $apiFunctionalRequirements;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getApiFunctionalRequirements()
  {
    return $this->apiFunctionalRequirements;
  }
  /**
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setApiRequirements(GoogleCloudApihubV1AttributeValues $apiRequirements)
  {
    $this->apiRequirements = $apiRequirements;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getApiRequirements()
  {
    return $this->apiRequirements;
  }
  /**
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setApiStyle(GoogleCloudApihubV1AttributeValues $apiStyle)
  {
    $this->apiStyle = $apiStyle;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getApiStyle()
  {
    return $this->apiStyle;
  }
  /**
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setApiTechnicalRequirements(GoogleCloudApihubV1AttributeValues $apiTechnicalRequirements)
  {
    $this->apiTechnicalRequirements = $apiTechnicalRequirements;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getApiTechnicalRequirements()
  {
    return $this->apiTechnicalRequirements;
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
  public function setBusinessUnit(GoogleCloudApihubV1AttributeValues $businessUnit)
  {
    $this->businessUnit = $businessUnit;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getBusinessUnit()
  {
    return $this->businessUnit;
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
   * @param string
   */
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  /**
   * @return string
   */
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  /**
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setMaturityLevel(GoogleCloudApihubV1AttributeValues $maturityLevel)
  {
    $this->maturityLevel = $maturityLevel;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getMaturityLevel()
  {
    return $this->maturityLevel;
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
   * @param GoogleCloudApihubV1Owner
   */
  public function setOwner(GoogleCloudApihubV1Owner $owner)
  {
    $this->owner = $owner;
  }
  /**
   * @return GoogleCloudApihubV1Owner
   */
  public function getOwner()
  {
    return $this->owner;
  }
  /**
   * @param string
   */
  public function setSelectedVersion($selectedVersion)
  {
    $this->selectedVersion = $selectedVersion;
  }
  /**
   * @return string
   */
  public function getSelectedVersion()
  {
    return $this->selectedVersion;
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
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setTargetUser(GoogleCloudApihubV1AttributeValues $targetUser)
  {
    $this->targetUser = $targetUser;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getTargetUser()
  {
    return $this->targetUser;
  }
  /**
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setTeam(GoogleCloudApihubV1AttributeValues $team)
  {
    $this->team = $team;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getTeam()
  {
    return $this->team;
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
  /**
   * @param string[]
   */
  public function setVersions($versions)
  {
    $this->versions = $versions;
  }
  /**
   * @return string[]
   */
  public function getVersions()
  {
    return $this->versions;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1Api::class, 'Google_Service_APIhub_GoogleCloudApihubV1Api');
