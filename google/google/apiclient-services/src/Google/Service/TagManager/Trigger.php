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

class Google_Service_TagManager_Trigger extends Google_Collection
{
  protected $collection_key = 'filter';
  public $accountId;
  protected $autoEventFilterType = 'Google_Service_TagManager_Condition';
  protected $autoEventFilterDataType = 'array';
  protected $checkValidationType = 'Google_Service_TagManager_Parameter';
  protected $checkValidationDataType = '';
  public $containerId;
  protected $customEventFilterType = 'Google_Service_TagManager_Condition';
  protected $customEventFilterDataType = 'array';
  protected $enableAllVideosType = 'Google_Service_TagManager_Parameter';
  protected $enableAllVideosDataType = '';
  protected $eventNameType = 'Google_Service_TagManager_Parameter';
  protected $eventNameDataType = '';
  protected $filterType = 'Google_Service_TagManager_Condition';
  protected $filterDataType = 'array';
  public $fingerprint;
  protected $intervalType = 'Google_Service_TagManager_Parameter';
  protected $intervalDataType = '';
  protected $limitType = 'Google_Service_TagManager_Parameter';
  protected $limitDataType = '';
  public $name;
  public $parentFolderId;
  public $triggerId;
  public $type;
  protected $uniqueTriggerIdType = 'Google_Service_TagManager_Parameter';
  protected $uniqueTriggerIdDataType = '';
  protected $videoPercentageListType = 'Google_Service_TagManager_Parameter';
  protected $videoPercentageListDataType = '';
  protected $waitForTagsType = 'Google_Service_TagManager_Parameter';
  protected $waitForTagsDataType = '';
  protected $waitForTagsTimeoutType = 'Google_Service_TagManager_Parameter';
  protected $waitForTagsTimeoutDataType = '';

  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setAutoEventFilter($autoEventFilter)
  {
    $this->autoEventFilter = $autoEventFilter;
  }
  public function getAutoEventFilter()
  {
    return $this->autoEventFilter;
  }
  public function setCheckValidation(Google_Service_TagManager_Parameter $checkValidation)
  {
    $this->checkValidation = $checkValidation;
  }
  public function getCheckValidation()
  {
    return $this->checkValidation;
  }
  public function setContainerId($containerId)
  {
    $this->containerId = $containerId;
  }
  public function getContainerId()
  {
    return $this->containerId;
  }
  public function setCustomEventFilter($customEventFilter)
  {
    $this->customEventFilter = $customEventFilter;
  }
  public function getCustomEventFilter()
  {
    return $this->customEventFilter;
  }
  public function setEnableAllVideos(Google_Service_TagManager_Parameter $enableAllVideos)
  {
    $this->enableAllVideos = $enableAllVideos;
  }
  public function getEnableAllVideos()
  {
    return $this->enableAllVideos;
  }
  public function setEventName(Google_Service_TagManager_Parameter $eventName)
  {
    $this->eventName = $eventName;
  }
  public function getEventName()
  {
    return $this->eventName;
  }
  public function setFilter($filter)
  {
    $this->filter = $filter;
  }
  public function getFilter()
  {
    return $this->filter;
  }
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setInterval(Google_Service_TagManager_Parameter $interval)
  {
    $this->interval = $interval;
  }
  public function getInterval()
  {
    return $this->interval;
  }
  public function setLimit(Google_Service_TagManager_Parameter $limit)
  {
    $this->limit = $limit;
  }
  public function getLimit()
  {
    return $this->limit;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setParentFolderId($parentFolderId)
  {
    $this->parentFolderId = $parentFolderId;
  }
  public function getParentFolderId()
  {
    return $this->parentFolderId;
  }
  public function setTriggerId($triggerId)
  {
    $this->triggerId = $triggerId;
  }
  public function getTriggerId()
  {
    return $this->triggerId;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setUniqueTriggerId(Google_Service_TagManager_Parameter $uniqueTriggerId)
  {
    $this->uniqueTriggerId = $uniqueTriggerId;
  }
  public function getUniqueTriggerId()
  {
    return $this->uniqueTriggerId;
  }
  public function setVideoPercentageList(Google_Service_TagManager_Parameter $videoPercentageList)
  {
    $this->videoPercentageList = $videoPercentageList;
  }
  public function getVideoPercentageList()
  {
    return $this->videoPercentageList;
  }
  public function setWaitForTags(Google_Service_TagManager_Parameter $waitForTags)
  {
    $this->waitForTags = $waitForTags;
  }
  public function getWaitForTags()
  {
    return $this->waitForTags;
  }
  public function setWaitForTagsTimeout(Google_Service_TagManager_Parameter $waitForTagsTimeout)
  {
    $this->waitForTagsTimeout = $waitForTagsTimeout;
  }
  public function getWaitForTagsTimeout()
  {
    return $this->waitForTagsTimeout;
  }
}
