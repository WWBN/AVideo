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

class Google_Service_Partners_CompanyRelation extends Google_Collection
{
  protected $collection_key = 'specializationStatus';
  public $address;
  public $badgeTier;
  public $companyAdmin;
  public $companyId;
  public $creationTime;
  public $isPending;
  public $logoUrl;
  public $managerAccount;
  public $name;
  public $phoneNumber;
  public $resolvedTimestamp;
  public $segment;
  protected $specializationStatusType = 'Google_Service_Partners_SpecializationStatus';
  protected $specializationStatusDataType = 'array';
  public $state;
  public $website;

  public function setAddress($address)
  {
    $this->address = $address;
  }
  public function getAddress()
  {
    return $this->address;
  }
  public function setBadgeTier($badgeTier)
  {
    $this->badgeTier = $badgeTier;
  }
  public function getBadgeTier()
  {
    return $this->badgeTier;
  }
  public function setCompanyAdmin($companyAdmin)
  {
    $this->companyAdmin = $companyAdmin;
  }
  public function getCompanyAdmin()
  {
    return $this->companyAdmin;
  }
  public function setCompanyId($companyId)
  {
    $this->companyId = $companyId;
  }
  public function getCompanyId()
  {
    return $this->companyId;
  }
  public function setCreationTime($creationTime)
  {
    $this->creationTime = $creationTime;
  }
  public function getCreationTime()
  {
    return $this->creationTime;
  }
  public function setIsPending($isPending)
  {
    $this->isPending = $isPending;
  }
  public function getIsPending()
  {
    return $this->isPending;
  }
  public function setLogoUrl($logoUrl)
  {
    $this->logoUrl = $logoUrl;
  }
  public function getLogoUrl()
  {
    return $this->logoUrl;
  }
  public function setManagerAccount($managerAccount)
  {
    $this->managerAccount = $managerAccount;
  }
  public function getManagerAccount()
  {
    return $this->managerAccount;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setPhoneNumber($phoneNumber)
  {
    $this->phoneNumber = $phoneNumber;
  }
  public function getPhoneNumber()
  {
    return $this->phoneNumber;
  }
  public function setResolvedTimestamp($resolvedTimestamp)
  {
    $this->resolvedTimestamp = $resolvedTimestamp;
  }
  public function getResolvedTimestamp()
  {
    return $this->resolvedTimestamp;
  }
  public function setSegment($segment)
  {
    $this->segment = $segment;
  }
  public function getSegment()
  {
    return $this->segment;
  }
  public function setSpecializationStatus($specializationStatus)
  {
    $this->specializationStatus = $specializationStatus;
  }
  public function getSpecializationStatus()
  {
    return $this->specializationStatus;
  }
  public function setState($state)
  {
    $this->state = $state;
  }
  public function getState()
  {
    return $this->state;
  }
  public function setWebsite($website)
  {
    $this->website = $website;
  }
  public function getWebsite()
  {
    return $this->website;
  }
}
