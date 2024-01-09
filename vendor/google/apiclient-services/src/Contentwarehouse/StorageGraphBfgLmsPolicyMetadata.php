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

namespace Google\Service\Contentwarehouse;

class StorageGraphBfgLmsPolicyMetadata extends \Google\Collection
{
  protected $collection_key = 'modificationsAllowed';
  /**
   * @var string[]
   */
  public $clientIdsAllowed;
  /**
   * @var bool
   */
  public $isEditorial;
  /**
   * @var string[]
   */
  public $modificationsAllowed;
  protected $regionsAllowedType = KeGovernanceTypedRegions::class;
  protected $regionsAllowedDataType = '';
  protected $regionsDisallowedType = KeGovernanceTypedRegions::class;
  protected $regionsDisallowedDataType = '';
  /**
   * @var bool
   */
  public $requiresAttribution;
  /**
   * @var bool
   */
  public $requiresFirstPartyOnly;
  /**
   * @var bool
   */
  public $requiresLinkback;
  /**
   * @var bool
   */
  public $requiresShareAlike;

  /**
   * @param string[]
   */
  public function setClientIdsAllowed($clientIdsAllowed)
  {
    $this->clientIdsAllowed = $clientIdsAllowed;
  }
  /**
   * @return string[]
   */
  public function getClientIdsAllowed()
  {
    return $this->clientIdsAllowed;
  }
  /**
   * @param bool
   */
  public function setIsEditorial($isEditorial)
  {
    $this->isEditorial = $isEditorial;
  }
  /**
   * @return bool
   */
  public function getIsEditorial()
  {
    return $this->isEditorial;
  }
  /**
   * @param string[]
   */
  public function setModificationsAllowed($modificationsAllowed)
  {
    $this->modificationsAllowed = $modificationsAllowed;
  }
  /**
   * @return string[]
   */
  public function getModificationsAllowed()
  {
    return $this->modificationsAllowed;
  }
  /**
   * @param KeGovernanceTypedRegions
   */
  public function setRegionsAllowed(KeGovernanceTypedRegions $regionsAllowed)
  {
    $this->regionsAllowed = $regionsAllowed;
  }
  /**
   * @return KeGovernanceTypedRegions
   */
  public function getRegionsAllowed()
  {
    return $this->regionsAllowed;
  }
  /**
   * @param KeGovernanceTypedRegions
   */
  public function setRegionsDisallowed(KeGovernanceTypedRegions $regionsDisallowed)
  {
    $this->regionsDisallowed = $regionsDisallowed;
  }
  /**
   * @return KeGovernanceTypedRegions
   */
  public function getRegionsDisallowed()
  {
    return $this->regionsDisallowed;
  }
  /**
   * @param bool
   */
  public function setRequiresAttribution($requiresAttribution)
  {
    $this->requiresAttribution = $requiresAttribution;
  }
  /**
   * @return bool
   */
  public function getRequiresAttribution()
  {
    return $this->requiresAttribution;
  }
  /**
   * @param bool
   */
  public function setRequiresFirstPartyOnly($requiresFirstPartyOnly)
  {
    $this->requiresFirstPartyOnly = $requiresFirstPartyOnly;
  }
  /**
   * @return bool
   */
  public function getRequiresFirstPartyOnly()
  {
    return $this->requiresFirstPartyOnly;
  }
  /**
   * @param bool
   */
  public function setRequiresLinkback($requiresLinkback)
  {
    $this->requiresLinkback = $requiresLinkback;
  }
  /**
   * @return bool
   */
  public function getRequiresLinkback()
  {
    return $this->requiresLinkback;
  }
  /**
   * @param bool
   */
  public function setRequiresShareAlike($requiresShareAlike)
  {
    $this->requiresShareAlike = $requiresShareAlike;
  }
  /**
   * @return bool
   */
  public function getRequiresShareAlike()
  {
    return $this->requiresShareAlike;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(StorageGraphBfgLmsPolicyMetadata::class, 'Google_Service_Contentwarehouse_StorageGraphBfgLmsPolicyMetadata');
