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

class GoogleAdsSearchads360V0ResourcesAdGroupCriterion extends \Google\Collection
{
  protected $collection_key = 'finalUrls';
  /**
   * @var string
   */
  public $adGroup;
  protected $ageRangeType = GoogleAdsSearchads360V0CommonAgeRangeInfo::class;
  protected $ageRangeDataType = '';
  public $ageRange;
  public $bidModifier;
  /**
   * @var string
   */
  public $cpcBidMicros;
  /**
   * @var string
   */
  public $criterionId;
  /**
   * @var string
   */
  public $effectiveCpcBidMicros;
  /**
   * @var string
   */
  public $engineStatus;
  /**
   * @var string
   */
  public $finalUrlSuffix;
  /**
   * @var string[]
   */
  public $finalUrls;
  protected $genderType = GoogleAdsSearchads360V0CommonGenderInfo::class;
  protected $genderDataType = '';
  public $gender;
  protected $keywordType = GoogleAdsSearchads360V0CommonKeywordInfo::class;
  protected $keywordDataType = '';
  public $keyword;
  /**
   * @var string
   */
  public $lastModifiedTime;
  protected $listingGroupType = GoogleAdsSearchads360V0CommonListingGroupInfo::class;
  protected $listingGroupDataType = '';
  public $listingGroup;
  protected $qualityInfoType = GoogleAdsSearchads360V0ResourcesAdGroupCriterionQualityInfo::class;
  protected $qualityInfoDataType = '';
  public $qualityInfo;
  /**
   * @var string
   */
  public $resourceName;
  /**
   * @var string
   */
  public $status;
  /**
   * @var string
   */
  public $trackingUrlTemplate;
  /**
   * @var string
   */
  public $type;
  protected $webpageType = GoogleAdsSearchads360V0CommonWebpageInfo::class;
  protected $webpageDataType = '';
  public $webpage;

  /**
   * @param string
   */
  public function setAdGroup($adGroup)
  {
    $this->adGroup = $adGroup;
  }
  /**
   * @return string
   */
  public function getAdGroup()
  {
    return $this->adGroup;
  }
  /**
   * @param GoogleAdsSearchads360V0CommonAgeRangeInfo
   */
  public function setAgeRange(GoogleAdsSearchads360V0CommonAgeRangeInfo $ageRange)
  {
    $this->ageRange = $ageRange;
  }
  /**
   * @return GoogleAdsSearchads360V0CommonAgeRangeInfo
   */
  public function getAgeRange()
  {
    return $this->ageRange;
  }
  public function setBidModifier($bidModifier)
  {
    $this->bidModifier = $bidModifier;
  }
  public function getBidModifier()
  {
    return $this->bidModifier;
  }
  /**
   * @param string
   */
  public function setCpcBidMicros($cpcBidMicros)
  {
    $this->cpcBidMicros = $cpcBidMicros;
  }
  /**
   * @return string
   */
  public function getCpcBidMicros()
  {
    return $this->cpcBidMicros;
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
   * @param string
   */
  public function setEffectiveCpcBidMicros($effectiveCpcBidMicros)
  {
    $this->effectiveCpcBidMicros = $effectiveCpcBidMicros;
  }
  /**
   * @return string
   */
  public function getEffectiveCpcBidMicros()
  {
    return $this->effectiveCpcBidMicros;
  }
  /**
   * @param string
   */
  public function setEngineStatus($engineStatus)
  {
    $this->engineStatus = $engineStatus;
  }
  /**
   * @return string
   */
  public function getEngineStatus()
  {
    return $this->engineStatus;
  }
  /**
   * @param string
   */
  public function setFinalUrlSuffix($finalUrlSuffix)
  {
    $this->finalUrlSuffix = $finalUrlSuffix;
  }
  /**
   * @return string
   */
  public function getFinalUrlSuffix()
  {
    return $this->finalUrlSuffix;
  }
  /**
   * @param string[]
   */
  public function setFinalUrls($finalUrls)
  {
    $this->finalUrls = $finalUrls;
  }
  /**
   * @return string[]
   */
  public function getFinalUrls()
  {
    return $this->finalUrls;
  }
  /**
   * @param GoogleAdsSearchads360V0CommonGenderInfo
   */
  public function setGender(GoogleAdsSearchads360V0CommonGenderInfo $gender)
  {
    $this->gender = $gender;
  }
  /**
   * @return GoogleAdsSearchads360V0CommonGenderInfo
   */
  public function getGender()
  {
    return $this->gender;
  }
  /**
   * @param GoogleAdsSearchads360V0CommonKeywordInfo
   */
  public function setKeyword(GoogleAdsSearchads360V0CommonKeywordInfo $keyword)
  {
    $this->keyword = $keyword;
  }
  /**
   * @return GoogleAdsSearchads360V0CommonKeywordInfo
   */
  public function getKeyword()
  {
    return $this->keyword;
  }
  /**
   * @param string
   */
  public function setLastModifiedTime($lastModifiedTime)
  {
    $this->lastModifiedTime = $lastModifiedTime;
  }
  /**
   * @return string
   */
  public function getLastModifiedTime()
  {
    return $this->lastModifiedTime;
  }
  /**
   * @param GoogleAdsSearchads360V0CommonListingGroupInfo
   */
  public function setListingGroup(GoogleAdsSearchads360V0CommonListingGroupInfo $listingGroup)
  {
    $this->listingGroup = $listingGroup;
  }
  /**
   * @return GoogleAdsSearchads360V0CommonListingGroupInfo
   */
  public function getListingGroup()
  {
    return $this->listingGroup;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesAdGroupCriterionQualityInfo
   */
  public function setQualityInfo(GoogleAdsSearchads360V0ResourcesAdGroupCriterionQualityInfo $qualityInfo)
  {
    $this->qualityInfo = $qualityInfo;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesAdGroupCriterionQualityInfo
   */
  public function getQualityInfo()
  {
    return $this->qualityInfo;
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
  public function setStatus($status)
  {
    $this->status = $status;
  }
  /**
   * @return string
   */
  public function getStatus()
  {
    return $this->status;
  }
  /**
   * @param string
   */
  public function setTrackingUrlTemplate($trackingUrlTemplate)
  {
    $this->trackingUrlTemplate = $trackingUrlTemplate;
  }
  /**
   * @return string
   */
  public function getTrackingUrlTemplate()
  {
    return $this->trackingUrlTemplate;
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
  /**
   * @param GoogleAdsSearchads360V0CommonWebpageInfo
   */
  public function setWebpage(GoogleAdsSearchads360V0CommonWebpageInfo $webpage)
  {
    $this->webpage = $webpage;
  }
  /**
   * @return GoogleAdsSearchads360V0CommonWebpageInfo
   */
  public function getWebpage()
  {
    return $this->webpage;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleAdsSearchads360V0ResourcesAdGroupCriterion::class, 'Google_Service_SA360_GoogleAdsSearchads360V0ResourcesAdGroupCriterion');
