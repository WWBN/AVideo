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

class Google_Service_CloudIdentity_GoogleAppsCloudidentityDevicesV1alpha1AndroidAttributes extends Google_Collection
{
  protected $collection_key = 'otherAccounts';
  public $basebandVersion;
  public $bootloaderVersion;
  public $buildNumber;
  public $enabledDeveloperOptions;
  public $enabledUnknownSources;
  public $enabledUsbDebugging;
  public $encryptionState;
  public $hardware;
  public $kernelVersion;
  public $otherAccounts;
  public $ownerProfileAccount;
  public $ownershipPrivilege;
  public $securityPatchTime;
  public $supportsWorkProfile;

  public function setBasebandVersion($basebandVersion)
  {
    $this->basebandVersion = $basebandVersion;
  }
  public function getBasebandVersion()
  {
    return $this->basebandVersion;
  }
  public function setBootloaderVersion($bootloaderVersion)
  {
    $this->bootloaderVersion = $bootloaderVersion;
  }
  public function getBootloaderVersion()
  {
    return $this->bootloaderVersion;
  }
  public function setBuildNumber($buildNumber)
  {
    $this->buildNumber = $buildNumber;
  }
  public function getBuildNumber()
  {
    return $this->buildNumber;
  }
  public function setEnabledDeveloperOptions($enabledDeveloperOptions)
  {
    $this->enabledDeveloperOptions = $enabledDeveloperOptions;
  }
  public function getEnabledDeveloperOptions()
  {
    return $this->enabledDeveloperOptions;
  }
  public function setEnabledUnknownSources($enabledUnknownSources)
  {
    $this->enabledUnknownSources = $enabledUnknownSources;
  }
  public function getEnabledUnknownSources()
  {
    return $this->enabledUnknownSources;
  }
  public function setEnabledUsbDebugging($enabledUsbDebugging)
  {
    $this->enabledUsbDebugging = $enabledUsbDebugging;
  }
  public function getEnabledUsbDebugging()
  {
    return $this->enabledUsbDebugging;
  }
  public function setEncryptionState($encryptionState)
  {
    $this->encryptionState = $encryptionState;
  }
  public function getEncryptionState()
  {
    return $this->encryptionState;
  }
  public function setHardware($hardware)
  {
    $this->hardware = $hardware;
  }
  public function getHardware()
  {
    return $this->hardware;
  }
  public function setKernelVersion($kernelVersion)
  {
    $this->kernelVersion = $kernelVersion;
  }
  public function getKernelVersion()
  {
    return $this->kernelVersion;
  }
  public function setOtherAccounts($otherAccounts)
  {
    $this->otherAccounts = $otherAccounts;
  }
  public function getOtherAccounts()
  {
    return $this->otherAccounts;
  }
  public function setOwnerProfileAccount($ownerProfileAccount)
  {
    $this->ownerProfileAccount = $ownerProfileAccount;
  }
  public function getOwnerProfileAccount()
  {
    return $this->ownerProfileAccount;
  }
  public function setOwnershipPrivilege($ownershipPrivilege)
  {
    $this->ownershipPrivilege = $ownershipPrivilege;
  }
  public function getOwnershipPrivilege()
  {
    return $this->ownershipPrivilege;
  }
  public function setSecurityPatchTime($securityPatchTime)
  {
    $this->securityPatchTime = $securityPatchTime;
  }
  public function getSecurityPatchTime()
  {
    return $this->securityPatchTime;
  }
  public function setSupportsWorkProfile($supportsWorkProfile)
  {
    $this->supportsWorkProfile = $supportsWorkProfile;
  }
  public function getSupportsWorkProfile()
  {
    return $this->supportsWorkProfile;
  }
}
