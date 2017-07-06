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

class Google_Service_Partners_User extends Google_Collection
{
  protected $collection_key = 'primaryEmails';
  protected $availableAdwordsManagerAccountsType = 'Google_Service_Partners_AdWordsManagerAccountInfo';
  protected $availableAdwordsManagerAccountsDataType = 'array';
  protected $certificationStatusType = 'Google_Service_Partners_Certification';
  protected $certificationStatusDataType = 'array';
  protected $companyType = 'Google_Service_Partners_CompanyRelation';
  protected $companyDataType = '';
  public $companyVerificationEmail;
  protected $examStatusType = 'Google_Service_Partners_ExamStatus';
  protected $examStatusDataType = 'array';
  public $id;
  public $lastAccessTime;
  public $primaryEmails;
  protected $profileType = 'Google_Service_Partners_UserProfile';
  protected $profileDataType = '';
  protected $publicProfileType = 'Google_Service_Partners_PublicProfile';
  protected $publicProfileDataType = '';

  public function setAvailableAdwordsManagerAccounts($availableAdwordsManagerAccounts)
  {
    $this->availableAdwordsManagerAccounts = $availableAdwordsManagerAccounts;
  }
  public function getAvailableAdwordsManagerAccounts()
  {
    return $this->availableAdwordsManagerAccounts;
  }
  public function setCertificationStatus($certificationStatus)
  {
    $this->certificationStatus = $certificationStatus;
  }
  public function getCertificationStatus()
  {
    return $this->certificationStatus;
  }
  public function setCompany(Google_Service_Partners_CompanyRelation $company)
  {
    $this->company = $company;
  }
  public function getCompany()
  {
    return $this->company;
  }
  public function setCompanyVerificationEmail($companyVerificationEmail)
  {
    $this->companyVerificationEmail = $companyVerificationEmail;
  }
  public function getCompanyVerificationEmail()
  {
    return $this->companyVerificationEmail;
  }
  public function setExamStatus($examStatus)
  {
    $this->examStatus = $examStatus;
  }
  public function getExamStatus()
  {
    return $this->examStatus;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setLastAccessTime($lastAccessTime)
  {
    $this->lastAccessTime = $lastAccessTime;
  }
  public function getLastAccessTime()
  {
    return $this->lastAccessTime;
  }
  public function setPrimaryEmails($primaryEmails)
  {
    $this->primaryEmails = $primaryEmails;
  }
  public function getPrimaryEmails()
  {
    return $this->primaryEmails;
  }
  public function setProfile(Google_Service_Partners_UserProfile $profile)
  {
    $this->profile = $profile;
  }
  public function getProfile()
  {
    return $this->profile;
  }
  public function setPublicProfile(Google_Service_Partners_PublicProfile $publicProfile)
  {
    $this->publicProfile = $publicProfile;
  }
  public function getPublicProfile()
  {
    return $this->publicProfile;
  }
}
