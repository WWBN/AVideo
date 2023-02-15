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

namespace Google\Service\SA360;

class GoogleAdsSearchads360V0ResourcesCampaignCriterion extends \Google\Model
{
  /**
   * @var float
   */
  public $bidModifier;
  /**
   * @var string
   */
  public $criterionId;
  protected $deviceType = GoogleAdsSearchads360V0CommonDeviceInfo::class;
  protected $deviceDataType = '';
  public $device;
  /**
   * @var string
   */
  public $displayName;
  protected $languageType = GoogleAdsSearchads360V0CommonLanguageInfo::class;
  protected $languageDataType = '';
  public $language;
  protected $locationType = GoogleAdsSearchads360V0CommonLocationInfo::class;
  protected $locationDataType = '';
  public $location;
  protected $locationGroupType = GoogleAdsSearchads360V0CommonLocationGroupInfo::class;
  protected $locationGroupDataType = '';
  public $locationGroup;
  /**
   * @var bool
   */
  public $negative;
  /**
   * @var string
   */
  public $resourceName;
  /**
   * @var string
   */
  public $type;

  /**
   * @param float
   */
  public function setBidModifier($bidModifier)
  {
    $this->bidModifier = $bidModifier;
  }
  /**
   * @return float
   */
  public function getBidModifier()
  {
    return $this->bidModifier;
  }
  /**
   * @param string
   */
  public function setCriterionId($criterionId)
  {
    $this->criterionId = $criterionId;
  }
  /**
   * @return string
   */
  public function getCriterionId()
  {
    return $this->criterionId;
  }
  /**
   * @param GoogleAdsSearchads360V0CommonDeviceInfo
   */
  public function setDevice(GoogleAdsSearchads360V0CommonDeviceInfo $device)
  {
    $this->device = $device;
  }
  /**
   * @return GoogleAdsSearchads360V0CommonDeviceInfo
   */
  public function getDevice()
  {
    return $this->device;
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
   * @param GoogleAdsSearchads360V0CommonLanguageInfo
   */
  public function setLanguage(GoogleAdsSearchads360V0CommonLanguageInfo $language)
  {
    $this->language = $language;
  }
  /**
   * @return GoogleAdsSearchads360V0CommonLanguageInfo
   */
  public function getLanguage()
  {
    return $this->language;
  }
  /**
   * @param GoogleAdsSearchads360V0CommonLocationInfo
   */
  public function setLocation(GoogleAdsSearchads360V0CommonLocationInfo $location)
  {
    $this->location = $location;
  }
  /**
   * @return GoogleAdsSearchads360V0CommonLocationInfo
   */
  public function getLocation()
  {
    return $this->location;
  }
  /**
   * @param GoogleAdsSearchads360V0CommonLocationGroupInfo
   */
  public function setLocationGroup(GoogleAdsSearchads360V0CommonLocationGroupInfo $locationGroup)
  {
    $this->locationGroup = $locationGroup;
  }
  /**
   * @return GoogleAdsSearchads360V0CommonLocationGroupInfo
   */
  public function getLocationGroup()
  {
    return $this->locationGroup;
  }
  /**
   * @param bool
   */
  public function setNegative($negative)
  {
    $this->negative = $negative;
  }
  /**
   * @return bool
   */
  public function getNegative()
  {
    return $this->negative;
  }
  /**
   * @param string
   */
  public function setResourceName($resourceName)
  {
    $this->resourceName = $resourceName;
  }
  /**
   * @return string
   */
  public function getResourceName()
  {
    return $this->resourceName;
  }
  /**
   * @param string
   */
  public function setType($type)
  {
    $this->type = $type;
  }
  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleAdsSearchads360V0ResourcesCampaignCriterion::class, 'Google_Service_SA360_GoogleAdsSearchads360V0ResourcesCampaignCriterion');
