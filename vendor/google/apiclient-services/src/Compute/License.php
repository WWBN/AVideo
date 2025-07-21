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

namespace Google\Service\Compute;

class License extends \Google\Collection
{
  protected $collection_key = 'requiredCoattachedLicenses';
  /**
   * @var string[]
   */
  public $allowedReplacementLicenses;
  /**
   * @var bool
   */
  public $appendableToDisk;
  /**
   * @var bool
   */
  public $chargesUseFee;
  /**
   * @var string
   */
  public $creationTimestamp;
  /**
   * @var string
   */
  public $description;
  /**
   * @var string
   */
  public $id;
  /**
   * @var string[]
   */
  public $incompatibleLicenses;
  /**
   * @var string
   */
  public $kind;
  /**
   * @var string
   */
  public $licenseCode;
  protected $minimumRetentionType = Duration::class;
  protected $minimumRetentionDataType = '';
  /**
   * @var bool
   */
  public $multiTenantOnly;
  /**
   * @var string
   */
  public $name;
  /**
   * @var bool
   */
  public $osLicense;
  /**
   * @var bool
   */
  public $removableFromDisk;
  /**
   * @var string[]
   */
  public $requiredCoattachedLicenses;
  protected $resourceRequirementsType = LicenseResourceRequirements::class;
  protected $resourceRequirementsDataType = '';
  /**
   * @var string
   */
  public $selfLink;
  /**
   * @var string
   */
  public $selfLinkWithId;
  /**
   * @var bool
   */
  public $soleTenantOnly;
  /**
   * @var bool
   */
  public $transferable;
  /**
   * @var string
   */
  public $updateTimestamp;

  /**
   * @param string[]
   */
  public function setAllowedReplacementLicenses($allowedReplacementLicenses)
  {
    $this->allowedReplacementLicenses = $allowedReplacementLicenses;
  }
  /**
   * @return string[]
   */
  public function getAllowedReplacementLicenses()
  {
    return $this->allowedReplacementLicenses;
  }
  /**
   * @param bool
   */
  public function setAppendableToDisk($appendableToDisk)
  {
    $this->appendableToDisk = $appendableToDisk;
  }
  /**
   * @return bool
   */
  public function getAppendableToDisk()
  {
    return $this->appendableToDisk;
  }
  /**
   * @param bool
   */
  public function setChargesUseFee($chargesUseFee)
  {
    $this->chargesUseFee = $chargesUseFee;
  }
  /**
   * @return bool
   */
  public function getChargesUseFee()
  {
    return $this->chargesUseFee;
  }
  /**
   * @param string
   */
  public function setCreationTimestamp($creationTimestamp)
  {
    $this->creationTimestamp = $creationTimestamp;
  }
  /**
   * @return string
   */
  public function getCreationTimestamp()
  {
    return $this->creationTimestamp;
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
  public function setId($id)
  {
    $this->id = $id;
  }
  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }
  /**
   * @param string[]
   */
  public function setIncompatibleLicenses($incompatibleLicenses)
  {
    $this->incompatibleLicenses = $incompatibleLicenses;
  }
  /**
   * @return string[]
   */
  public function getIncompatibleLicenses()
  {
    return $this->incompatibleLicenses;
  }
  /**
   * @param string
   */
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  /**
   * @return string
   */
  public function getKind()
  {
    return $this->kind;
  }
  /**
   * @param string
   */
  public function setLicenseCode($licenseCode)
  {
    $this->licenseCode = $licenseCode;
  }
  /**
   * @return string
   */
  public function getLicenseCode()
  {
    return $this->licenseCode;
  }
  /**
   * @param Duration
   */
  public function setMinimumRetention(Duration $minimumRetention)
  {
    $this->minimumRetention = $minimumRetention;
  }
  /**
   * @return Duration
   */
  public function getMinimumRetention()
  {
    return $this->minimumRetention;
  }
  /**
   * @param bool
   */
  public function setMultiTenantOnly($multiTenantOnly)
  {
    $this->multiTenantOnly = $multiTenantOnly;
  }
  /**
   * @return bool
   */
  public function getMultiTenantOnly()
  {
    return $this->multiTenantOnly;
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
  public function setOsLicense($osLicense)
  {
    $this->osLicense = $osLicense;
  }
  /**
   * @return bool
   */
  public function getOsLicense()
  {
    return $this->osLicense;
  }
  /**
   * @param bool
   */
  public function setRemovableFromDisk($removableFromDisk)
  {
    $this->removableFromDisk = $removableFromDisk;
  }
  /**
   * @return bool
   */
  public function getRemovableFromDisk()
  {
    return $this->removableFromDisk;
  }
  /**
   * @param string[]
   */
  public function setRequiredCoattachedLicenses($requiredCoattachedLicenses)
  {
    $this->requiredCoattachedLicenses = $requiredCoattachedLicenses;
  }
  /**
   * @return string[]
   */
  public function getRequiredCoattachedLicenses()
  {
    return $this->requiredCoattachedLicenses;
  }
  /**
   * @param LicenseResourceRequirements
   */
  public function setResourceRequirements(LicenseResourceRequirements $resourceRequirements)
  {
    $this->resourceRequirements = $resourceRequirements;
  }
  /**
   * @return LicenseResourceRequirements
   */
  public function getResourceRequirements()
  {
    return $this->resourceRequirements;
  }
  /**
   * @param string
   */
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  /**
   * @return string
   */
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  /**
   * @param string
   */
  public function setSelfLinkWithId($selfLinkWithId)
  {
    $this->selfLinkWithId = $selfLinkWithId;
  }
  /**
   * @return string
   */
  public function getSelfLinkWithId()
  {
    return $this->selfLinkWithId;
  }
  /**
   * @param bool
   */
  public function setSoleTenantOnly($soleTenantOnly)
  {
    $this->soleTenantOnly = $soleTenantOnly;
  }
  /**
   * @return bool
   */
  public function getSoleTenantOnly()
  {
    return $this->soleTenantOnly;
  }
  /**
   * @param bool
   */
  public function setTransferable($transferable)
  {
    $this->transferable = $transferable;
  }
  /**
   * @return bool
   */
  public function getTransferable()
  {
    return $this->transferable;
  }
  /**
   * @param string
   */
  public function setUpdateTimestamp($updateTimestamp)
  {
    $this->updateTimestamp = $updateTimestamp;
  }
  /**
   * @return string
   */
  public function getUpdateTimestamp()
  {
    return $this->updateTimestamp;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(License::class, 'Google_Service_Compute_License');
