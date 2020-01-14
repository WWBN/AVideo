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

class Google_Service_TagManager_Container extends Google_Collection
{
  protected $collection_key = 'usageContext';
  public $accountId;
  public $containerId;
  public $domainName;
  public $enabledBuiltInVariable;
  public $fingerprint;
  public $name;
  public $notes;
  public $publicId;
  public $timeZoneCountryId;
  public $timeZoneId;
  public $usageContext;

  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setContainerId($containerId)
  {
    $this->containerId = $containerId;
  }
  public function getContainerId()
  {
    return $this->containerId;
  }
  public function setDomainName($domainName)
  {
    $this->domainName = $domainName;
  }
  public function getDomainName()
  {
    return $this->domainName;
  }
  public function setEnabledBuiltInVariable($enabledBuiltInVariable)
  {
    $this->enabledBuiltInVariable = $enabledBuiltInVariable;
  }
  public function getEnabledBuiltInVariable()
  {
    return $this->enabledBuiltInVariable;
  }
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setNotes($notes)
  {
    $this->notes = $notes;
  }
  public function getNotes()
  {
    return $this->notes;
  }
  public function setPublicId($publicId)
  {
    $this->publicId = $publicId;
  }
  public function getPublicId()
  {
    return $this->publicId;
  }
  public function setTimeZoneCountryId($timeZoneCountryId)
  {
    $this->timeZoneCountryId = $timeZoneCountryId;
  }
  public function getTimeZoneCountryId()
  {
    return $this->timeZoneCountryId;
  }
  public function setTimeZoneId($timeZoneId)
  {
    $this->timeZoneId = $timeZoneId;
  }
  public function getTimeZoneId()
  {
    return $this->timeZoneId;
  }
  public function setUsageContext($usageContext)
  {
    $this->usageContext = $usageContext;
  }
  public function getUsageContext()
  {
    return $this->usageContext;
  }
}
