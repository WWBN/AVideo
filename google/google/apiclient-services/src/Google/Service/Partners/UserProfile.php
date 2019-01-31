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

class Google_Service_Partners_UserProfile extends Google_Collection
{
  protected $collection_key = 'markets';
  protected $addressType = 'Google_Service_Partners_Location';
  protected $addressDataType = '';
  public $adwordsManagerAccount;
  public $channels;
  public $emailAddress;
  protected $emailOptInsType = 'Google_Service_Partners_OptIns';
  protected $emailOptInsDataType = '';
  public $familyName;
  public $givenName;
  public $industries;
  public $jobFunctions;
  public $languages;
  public $markets;
  public $phoneNumber;
  public $primaryCountryCode;
  public $profilePublic;

  public function setAddress(Google_Service_Partners_Location $address)
  {
    $this->address = $address;
  }
  public function getAddress()
  {
    return $this->address;
  }
  public function setAdwordsManagerAccount($adwordsManagerAccount)
  {
    $this->adwordsManagerAccount = $adwordsManagerAccount;
  }
  public function getAdwordsManagerAccount()
  {
    return $this->adwordsManagerAccount;
  }
  public function setChannels($channels)
  {
    $this->channels = $channels;
  }
  public function getChannels()
  {
    return $this->channels;
  }
  public function setEmailAddress($emailAddress)
  {
    $this->emailAddress = $emailAddress;
  }
  public function getEmailAddress()
  {
    return $this->emailAddress;
  }
  public function setEmailOptIns(Google_Service_Partners_OptIns $emailOptIns)
  {
    $this->emailOptIns = $emailOptIns;
  }
  public function getEmailOptIns()
  {
    return $this->emailOptIns;
  }
  public function setFamilyName($familyName)
  {
    $this->familyName = $familyName;
  }
  public function getFamilyName()
  {
    return $this->familyName;
  }
  public function setGivenName($givenName)
  {
    $this->givenName = $givenName;
  }
  public function getGivenName()
  {
    return $this->givenName;
  }
  public function setIndustries($industries)
  {
    $this->industries = $industries;
  }
  public function getIndustries()
  {
    return $this->industries;
  }
  public function setJobFunctions($jobFunctions)
  {
    $this->jobFunctions = $jobFunctions;
  }
  public function getJobFunctions()
  {
    return $this->jobFunctions;
  }
  public function setLanguages($languages)
  {
    $this->languages = $languages;
  }
  public function getLanguages()
  {
    return $this->languages;
  }
  public function setMarkets($markets)
  {
    $this->markets = $markets;
  }
  public function getMarkets()
  {
    return $this->markets;
  }
  public function setPhoneNumber($phoneNumber)
  {
    $this->phoneNumber = $phoneNumber;
  }
  public function getPhoneNumber()
  {
    return $this->phoneNumber;
  }
  public function setPrimaryCountryCode($primaryCountryCode)
  {
    $this->primaryCountryCode = $primaryCountryCode;
  }
  public function getPrimaryCountryCode()
  {
    return $this->primaryCountryCode;
  }
  public function setProfilePublic($profilePublic)
  {
    $this->profilePublic = $profilePublic;
  }
  public function getProfilePublic()
  {
    return $this->profilePublic;
  }
}
