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

namespace Google\Service\DiscoveryEngine;

class GoogleCloudDiscoveryengineV1UserLicense extends \Google\Model
{
  /**
   * @var string
   */
  public $createTime;
  /**
   * @var string
   */
  public $lastLoginTime;
  /**
   * @var string
   */
  public $licenseAssignmentState;
  /**
   * @var string
   */
  public $licenseConfig;
  /**
   * @var string
   */
  public $updateTime;
  /**
   * @var string
   */
  public $userPrincipal;
  /**
   * @var string
   */
  public $userProfile;

  /**
   * @param string
   */
  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  /**
   * @return string
   */
  public function getCreateTime()
  {
    return $this->createTime;
  }
  /**
   * @param string
   */
  public function setLastLoginTime($lastLoginTime)
  {
    $this->lastLoginTime = $lastLoginTime;
  }
  /**
   * @return string
   */
  public function getLastLoginTime()
  {
    return $this->lastLoginTime;
  }
  /**
   * @param string
   */
  public function setLicenseAssignmentState($licenseAssignmentState)
  {
    $this->licenseAssignmentState = $licenseAssignmentState;
  }
  /**
   * @return string
   */
  public function getLicenseAssignmentState()
  {
    return $this->licenseAssignmentState;
  }
  /**
   * @param string
   */
  public function setLicenseConfig($licenseConfig)
  {
    $this->licenseConfig = $licenseConfig;
  }
  /**
   * @return string
   */
  public function getLicenseConfig()
  {
    return $this->licenseConfig;
  }
  /**
   * @param string
   */
  public function setUpdateTime($updateTime)
  {
    $this->updateTime = $updateTime;
  }
  /**
   * @return string
   */
  public function getUpdateTime()
  {
    return $this->updateTime;
  }
  /**
   * @param string
   */
  public function setUserPrincipal($userPrincipal)
  {
    $this->userPrincipal = $userPrincipal;
  }
  /**
   * @return string
   */
  public function getUserPrincipal()
  {
    return $this->userPrincipal;
  }
  /**
   * @param string
   */
  public function setUserProfile($userProfile)
  {
    $this->userProfile = $userProfile;
  }
  /**
   * @return string
   */
  public function getUserProfile()
  {
    return $this->userProfile;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDiscoveryengineV1UserLicense::class, 'Google_Service_DiscoveryEngine_GoogleCloudDiscoveryengineV1UserLicense');
