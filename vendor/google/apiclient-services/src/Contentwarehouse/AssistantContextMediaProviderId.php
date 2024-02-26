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

namespace Google\Service\Contentwarehouse;

class AssistantContextMediaProviderId extends \Google\Model
{
  /**
   * @var string
   */
  public $androidPackageName;
  /**
   * @var string
   */
  public $castAppId;
  /**
   * @var string
   */
  public $chromeOsPackageName;
  /**
   * @var string
   */
  public $homeAppPackageName;
  /**
   * @var string
   */
  public $iosBundleIdentifier;
  /**
   * @var string
   */
  public $kaiOsPackageName;
  /**
   * @var string
   */
  public $kgProviderKey;
  /**
   * @var string
   */
  public $mid;
  protected $providerVariantType = AssistantContextProviderVariant::class;
  protected $providerVariantDataType = '';
  /**
   * @var string
   */
  public $sipProviderId;

  /**
   * @param string
   */
  public function setAndroidPackageName($androidPackageName)
  {
    $this->androidPackageName = $androidPackageName;
  }
  /**
   * @return string
   */
  public function getAndroidPackageName()
  {
    return $this->androidPackageName;
  }
  /**
   * @param string
   */
  public function setCastAppId($castAppId)
  {
    $this->castAppId = $castAppId;
  }
  /**
   * @return string
   */
  public function getCastAppId()
  {
    return $this->castAppId;
  }
  /**
   * @param string
   */
  public function setChromeOsPackageName($chromeOsPackageName)
  {
    $this->chromeOsPackageName = $chromeOsPackageName;
  }
  /**
   * @return string
   */
  public function getChromeOsPackageName()
  {
    return $this->chromeOsPackageName;
  }
  /**
   * @param string
   */
  public function setHomeAppPackageName($homeAppPackageName)
  {
    $this->homeAppPackageName = $homeAppPackageName;
  }
  /**
   * @return string
   */
  public function getHomeAppPackageName()
  {
    return $this->homeAppPackageName;
  }
  /**
   * @param string
   */
  public function setIosBundleIdentifier($iosBundleIdentifier)
  {
    $this->iosBundleIdentifier = $iosBundleIdentifier;
  }
  /**
   * @return string
   */
  public function getIosBundleIdentifier()
  {
    return $this->iosBundleIdentifier;
  }
  /**
   * @param string
   */
  public function setKaiOsPackageName($kaiOsPackageName)
  {
    $this->kaiOsPackageName = $kaiOsPackageName;
  }
  /**
   * @return string
   */
  public function getKaiOsPackageName()
  {
    return $this->kaiOsPackageName;
  }
  /**
   * @param string
   */
  public function setKgProviderKey($kgProviderKey)
  {
    $this->kgProviderKey = $kgProviderKey;
  }
  /**
   * @return string
   */
  public function getKgProviderKey()
  {
    return $this->kgProviderKey;
  }
  /**
   * @param string
   */
  public function setMid($mid)
  {
    $this->mid = $mid;
  }
  /**
   * @return string
   */
  public function getMid()
  {
    return $this->mid;
  }
  /**
   * @param AssistantContextProviderVariant
   */
  public function setProviderVariant(AssistantContextProviderVariant $providerVariant)
  {
    $this->providerVariant = $providerVariant;
  }
  /**
   * @return AssistantContextProviderVariant
   */
  public function getProviderVariant()
  {
    return $this->providerVariant;
  }
  /**
   * @param string
   */
  public function setSipProviderId($sipProviderId)
  {
    $this->sipProviderId = $sipProviderId;
  }
  /**
   * @return string
   */
  public function getSipProviderId()
  {
    return $this->sipProviderId;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantContextMediaProviderId::class, 'Google_Service_Contentwarehouse_AssistantContextMediaProviderId');
