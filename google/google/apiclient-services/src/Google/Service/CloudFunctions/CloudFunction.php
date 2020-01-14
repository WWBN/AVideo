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

class Google_Service_CloudFunctions_CloudFunction extends Google_Model
{
  public $availableMemoryMb;
  public $entryPoint;
  protected $eventTriggerType = 'Google_Service_CloudFunctions_EventTrigger';
  protected $eventTriggerDataType = '';
  protected $httpsTriggerType = 'Google_Service_CloudFunctions_HTTPSTrigger';
  protected $httpsTriggerDataType = '';
  public $latestOperation;
  public $name;
  public $serviceAccount;
  public $sourceArchiveUrl;
  protected $sourceRepositoryType = 'Google_Service_CloudFunctions_SourceRepository';
  protected $sourceRepositoryDataType = '';
  public $status;
  public $timeout;
  public $updateTime;

  public function setAvailableMemoryMb($availableMemoryMb)
  {
    $this->availableMemoryMb = $availableMemoryMb;
  }
  public function getAvailableMemoryMb()
  {
    return $this->availableMemoryMb;
  }
  public function setEntryPoint($entryPoint)
  {
    $this->entryPoint = $entryPoint;
  }
  public function getEntryPoint()
  {
    return $this->entryPoint;
  }
  public function setEventTrigger(Google_Service_CloudFunctions_EventTrigger $eventTrigger)
  {
    $this->eventTrigger = $eventTrigger;
  }
  public function getEventTrigger()
  {
    return $this->eventTrigger;
  }
  public function setHttpsTrigger(Google_Service_CloudFunctions_HTTPSTrigger $httpsTrigger)
  {
    $this->httpsTrigger = $httpsTrigger;
  }
  public function getHttpsTrigger()
  {
    return $this->httpsTrigger;
  }
  public function setLatestOperation($latestOperation)
  {
    $this->latestOperation = $latestOperation;
  }
  public function getLatestOperation()
  {
    return $this->latestOperation;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setServiceAccount($serviceAccount)
  {
    $this->serviceAccount = $serviceAccount;
  }
  public function getServiceAccount()
  {
    return $this->serviceAccount;
  }
  public function setSourceArchiveUrl($sourceArchiveUrl)
  {
    $this->sourceArchiveUrl = $sourceArchiveUrl;
  }
  public function getSourceArchiveUrl()
  {
    return $this->sourceArchiveUrl;
  }
  public function setSourceRepository(Google_Service_CloudFunctions_SourceRepository $sourceRepository)
  {
    $this->sourceRepository = $sourceRepository;
  }
  public function getSourceRepository()
  {
    return $this->sourceRepository;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setTimeout($timeout)
  {
    $this->timeout = $timeout;
  }
  public function getTimeout()
  {
    return $this->timeout;
  }
  public function setUpdateTime($updateTime)
  {
    $this->updateTime = $updateTime;
  }
  public function getUpdateTime()
  {
    return $this->updateTime;
  }
}
