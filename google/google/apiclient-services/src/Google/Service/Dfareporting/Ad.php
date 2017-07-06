<?php
/*
 * Copyright 2016 Google Inc.
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

class Google_Service_Dfareporting_Ad extends Google_Collection
{
  protected $collection_key = 'placementAssignments';
  public $accountId;
  public $active;
  public $advertiserId;
  protected $advertiserIdDimensionValueType = 'Google_Service_Dfareporting_DimensionValue';
  protected $advertiserIdDimensionValueDataType = '';
  public $archived;
  public $audienceSegmentId;
  public $campaignId;
  protected $campaignIdDimensionValueType = 'Google_Service_Dfareporting_DimensionValue';
  protected $campaignIdDimensionValueDataType = '';
  protected $clickThroughUrlType = 'Google_Service_Dfareporting_ClickThroughUrl';
  protected $clickThroughUrlDataType = '';
  protected $clickThroughUrlSuffixPropertiesType = 'Google_Service_Dfareporting_ClickThroughUrlSuffixProperties';
  protected $clickThroughUrlSuffixPropertiesDataType = '';
  public $comments;
  public $compatibility;
  protected $createInfoType = 'Google_Service_Dfareporting_LastModifiedInfo';
  protected $createInfoDataType = '';
  protected $creativeGroupAssignmentsType = 'Google_Service_Dfareporting_CreativeGroupAssignment';
  protected $creativeGroupAssignmentsDataType = 'array';
  protected $creativeRotationType = 'Google_Service_Dfareporting_CreativeRotation';
  protected $creativeRotationDataType = '';
  protected $dayPartTargetingType = 'Google_Service_Dfareporting_DayPartTargeting';
  protected $dayPartTargetingDataType = '';
  protected $defaultClickThroughEventTagPropertiesType = 'Google_Service_Dfareporting_DefaultClickThroughEventTagProperties';
  protected $defaultClickThroughEventTagPropertiesDataType = '';
  protected $deliveryScheduleType = 'Google_Service_Dfareporting_DeliverySchedule';
  protected $deliveryScheduleDataType = '';
  public $dynamicClickTracker;
  public $endTime;
  protected $eventTagOverridesType = 'Google_Service_Dfareporting_EventTagOverride';
  protected $eventTagOverridesDataType = 'array';
  protected $geoTargetingType = 'Google_Service_Dfareporting_GeoTargeting';
  protected $geoTargetingDataType = '';
  public $id;
  protected $idDimensionValueType = 'Google_Service_Dfareporting_DimensionValue';
  protected $idDimensionValueDataType = '';
  protected $keyValueTargetingExpressionType = 'Google_Service_Dfareporting_KeyValueTargetingExpression';
  protected $keyValueTargetingExpressionDataType = '';
  public $kind;
  protected $languageTargetingType = 'Google_Service_Dfareporting_LanguageTargeting';
  protected $languageTargetingDataType = '';
  protected $lastModifiedInfoType = 'Google_Service_Dfareporting_LastModifiedInfo';
  protected $lastModifiedInfoDataType = '';
  public $name;
  protected $placementAssignmentsType = 'Google_Service_Dfareporting_PlacementAssignment';
  protected $placementAssignmentsDataType = 'array';
  protected $remarketingListExpressionType = 'Google_Service_Dfareporting_ListTargetingExpression';
  protected $remarketingListExpressionDataType = '';
  protected $sizeType = 'Google_Service_Dfareporting_Size';
  protected $sizeDataType = '';
  public $sslCompliant;
  public $sslRequired;
  public $startTime;
  public $subaccountId;
  public $targetingTemplateId;
  protected $technologyTargetingType = 'Google_Service_Dfareporting_TechnologyTargeting';
  protected $technologyTargetingDataType = '';
  public $type;

  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setActive($active)
  {
    $this->active = $active;
  }
  public function getActive()
  {
    return $this->active;
  }
  public function setAdvertiserId($advertiserId)
  {
    $this->advertiserId = $advertiserId;
  }
  public function getAdvertiserId()
  {
    return $this->advertiserId;
  }
  public function setAdvertiserIdDimensionValue(Google_Service_Dfareporting_DimensionValue $advertiserIdDimensionValue)
  {
    $this->advertiserIdDimensionValue = $advertiserIdDimensionValue;
  }
  public function getAdvertiserIdDimensionValue()
  {
    return $this->advertiserIdDimensionValue;
  }
  public function setArchived($archived)
  {
    $this->archived = $archived;
  }
  public function getArchived()
  {
    return $this->archived;
  }
  public function setAudienceSegmentId($audienceSegmentId)
  {
    $this->audienceSegmentId = $audienceSegmentId;
  }
  public function getAudienceSegmentId()
  {
    return $this->audienceSegmentId;
  }
  public function setCampaignId($campaignId)
  {
    $this->campaignId = $campaignId;
  }
  public function getCampaignId()
  {
    return $this->campaignId;
  }
  public function setCampaignIdDimensionValue(Google_Service_Dfareporting_DimensionValue $campaignIdDimensionValue)
  {
    $this->campaignIdDimensionValue = $campaignIdDimensionValue;
  }
  public function getCampaignIdDimensionValue()
  {
    return $this->campaignIdDimensionValue;
  }
  public function setClickThroughUrl(Google_Service_Dfareporting_ClickThroughUrl $clickThroughUrl)
  {
    $this->clickThroughUrl = $clickThroughUrl;
  }
  public function getClickThroughUrl()
  {
    return $this->clickThroughUrl;
  }
  public function setClickThroughUrlSuffixProperties(Google_Service_Dfareporting_ClickThroughUrlSuffixProperties $clickThroughUrlSuffixProperties)
  {
    $this->clickThroughUrlSuffixProperties = $clickThroughUrlSuffixProperties;
  }
  public function getClickThroughUrlSuffixProperties()
  {
    return $this->clickThroughUrlSuffixProperties;
  }
  public function setComments($comments)
  {
    $this->comments = $comments;
  }
  public function getComments()
  {
    return $this->comments;
  }
  public function setCompatibility($compatibility)
  {
    $this->compatibility = $compatibility;
  }
  public function getCompatibility()
  {
    return $this->compatibility;
  }
  public function setCreateInfo(Google_Service_Dfareporting_LastModifiedInfo $createInfo)
  {
    $this->createInfo = $createInfo;
  }
  public function getCreateInfo()
  {
    return $this->createInfo;
  }
  public function setCreativeGroupAssignments($creativeGroupAssignments)
  {
    $this->creativeGroupAssignments = $creativeGroupAssignments;
  }
  public function getCreativeGroupAssignments()
  {
    return $this->creativeGroupAssignments;
  }
  public function setCreativeRotation(Google_Service_Dfareporting_CreativeRotation $creativeRotation)
  {
    $this->creativeRotation = $creativeRotation;
  }
  public function getCreativeRotation()
  {
    return $this->creativeRotation;
  }
  public function setDayPartTargeting(Google_Service_Dfareporting_DayPartTargeting $dayPartTargeting)
  {
    $this->dayPartTargeting = $dayPartTargeting;
  }
  public function getDayPartTargeting()
  {
    return $this->dayPartTargeting;
  }
  public function setDefaultClickThroughEventTagProperties(Google_Service_Dfareporting_DefaultClickThroughEventTagProperties $defaultClickThroughEventTagProperties)
  {
    $this->defaultClickThroughEventTagProperties = $defaultClickThroughEventTagProperties;
  }
  public function getDefaultClickThroughEventTagProperties()
  {
    return $this->defaultClickThroughEventTagProperties;
  }
  public function setDeliverySchedule(Google_Service_Dfareporting_DeliverySchedule $deliverySchedule)
  {
    $this->deliverySchedule = $deliverySchedule;
  }
  public function getDeliverySchedule()
  {
    return $this->deliverySchedule;
  }
  public function setDynamicClickTracker($dynamicClickTracker)
  {
    $this->dynamicClickTracker = $dynamicClickTracker;
  }
  public function getDynamicClickTracker()
  {
    return $this->dynamicClickTracker;
  }
  public function setEndTime($endTime)
  {
    $this->endTime = $endTime;
  }
  public function getEndTime()
  {
    return $this->endTime;
  }
  public function setEventTagOverrides($eventTagOverrides)
  {
    $this->eventTagOverrides = $eventTagOverrides;
  }
  public function getEventTagOverrides()
  {
    return $this->eventTagOverrides;
  }
  public function setGeoTargeting(Google_Service_Dfareporting_GeoTargeting $geoTargeting)
  {
    $this->geoTargeting = $geoTargeting;
  }
  public function getGeoTargeting()
  {
    return $this->geoTargeting;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setIdDimensionValue(Google_Service_Dfareporting_DimensionValue $idDimensionValue)
  {
    $this->idDimensionValue = $idDimensionValue;
  }
  public function getIdDimensionValue()
  {
    return $this->idDimensionValue;
  }
  public function setKeyValueTargetingExpression(Google_Service_Dfareporting_KeyValueTargetingExpression $keyValueTargetingExpression)
  {
    $this->keyValueTargetingExpression = $keyValueTargetingExpression;
  }
  public function getKeyValueTargetingExpression()
  {
    return $this->keyValueTargetingExpression;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLanguageTargeting(Google_Service_Dfareporting_LanguageTargeting $languageTargeting)
  {
    $this->languageTargeting = $languageTargeting;
  }
  public function getLanguageTargeting()
  {
    return $this->languageTargeting;
  }
  public function setLastModifiedInfo(Google_Service_Dfareporting_LastModifiedInfo $lastModifiedInfo)
  {
    $this->lastModifiedInfo = $lastModifiedInfo;
  }
  public function getLastModifiedInfo()
  {
    return $this->lastModifiedInfo;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setPlacementAssignments($placementAssignments)
  {
    $this->placementAssignments = $placementAssignments;
  }
  public function getPlacementAssignments()
  {
    return $this->placementAssignments;
  }
  public function setRemarketingListExpression(Google_Service_Dfareporting_ListTargetingExpression $remarketingListExpression)
  {
    $this->remarketingListExpression = $remarketingListExpression;
  }
  public function getRemarketingListExpression()
  {
    return $this->remarketingListExpression;
  }
  public function setSize(Google_Service_Dfareporting_Size $size)
  {
    $this->size = $size;
  }
  public function getSize()
  {
    return $this->size;
  }
  public function setSslCompliant($sslCompliant)
  {
    $this->sslCompliant = $sslCompliant;
  }
  public function getSslCompliant()
  {
    return $this->sslCompliant;
  }
  public function setSslRequired($sslRequired)
  {
    $this->sslRequired = $sslRequired;
  }
  public function getSslRequired()
  {
    return $this->sslRequired;
  }
  public function setStartTime($startTime)
  {
    $this->startTime = $startTime;
  }
  public function getStartTime()
  {
    return $this->startTime;
  }
  public function setSubaccountId($subaccountId)
  {
    $this->subaccountId = $subaccountId;
  }
  public function getSubaccountId()
  {
    return $this->subaccountId;
  }
  public function setTargetingTemplateId($targetingTemplateId)
  {
    $this->targetingTemplateId = $targetingTemplateId;
  }
  public function getTargetingTemplateId()
  {
    return $this->targetingTemplateId;
  }
  public function setTechnologyTargeting(Google_Service_Dfareporting_TechnologyTargeting $technologyTargeting)
  {
    $this->technologyTargeting = $technologyTargeting;
  }
  public function getTechnologyTargeting()
  {
    return $this->technologyTargeting;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
}
