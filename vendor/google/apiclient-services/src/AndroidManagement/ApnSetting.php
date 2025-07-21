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

namespace Google\Service\AndroidManagement;

class ApnSetting extends \Google\Collection
{
  protected $collection_key = 'networkTypes';
  /**
   * @var string
   */
  public $alwaysOnSetting;
  /**
   * @var string
   */
  public $apn;
  /**
   * @var string[]
   */
  public $apnTypes;
  /**
   * @var string
   */
  public $authType;
  /**
   * @var int
   */
  public $carrierId;
  /**
   * @var string
   */
  public $displayName;
  /**
   * @var string
   */
  public $mmsProxyAddress;
  /**
   * @var int
   */
  public $mmsProxyPort;
  /**
   * @var string
   */
  public $mmsc;
  /**
   * @var int
   */
  public $mtuV4;
  /**
   * @var int
   */
  public $mtuV6;
  /**
   * @var string
   */
  public $mvnoType;
  /**
   * @var string[]
   */
  public $networkTypes;
  /**
   * @var string
   */
  public $numericOperatorId;
  /**
   * @var string
   */
  public $password;
  /**
   * @var string
   */
  public $protocol;
  /**
   * @var string
   */
  public $proxyAddress;
  /**
   * @var int
   */
  public $proxyPort;
  /**
   * @var string
   */
  public $roamingProtocol;
  /**
   * @var string
   */
  public $username;

  /**
   * @param string
   */
  public function setAlwaysOnSetting($alwaysOnSetting)
  {
    $this->alwaysOnSetting = $alwaysOnSetting;
  }
  /**
   * @return string
   */
  public function getAlwaysOnSetting()
  {
    return $this->alwaysOnSetting;
  }
  /**
   * @param string
   */
  public function setApn($apn)
  {
    $this->apn = $apn;
  }
  /**
   * @return string
   */
  public function getApn()
  {
    return $this->apn;
  }
  /**
   * @param string[]
   */
  public function setApnTypes($apnTypes)
  {
    $this->apnTypes = $apnTypes;
  }
  /**
   * @return string[]
   */
  public function getApnTypes()
  {
    return $this->apnTypes;
  }
  /**
   * @param string
   */
  public function setAuthType($authType)
  {
    $this->authType = $authType;
  }
  /**
   * @return string
   */
  public function getAuthType()
  {
    return $this->authType;
  }
  /**
   * @param int
   */
  public function setCarrierId($carrierId)
  {
    $this->carrierId = $carrierId;
  }
  /**
   * @return int
   */
  public function getCarrierId()
  {
    return $this->carrierId;
  }
  /**
   * @param string
   */
  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  /**
   * @return string
   */
  public function getDisplayName()
  {
    return $this->displayName;
  }
  /**
   * @param string
   */
  public function setMmsProxyAddress($mmsProxyAddress)
  {
    $this->mmsProxyAddress = $mmsProxyAddress;
  }
  /**
   * @return string
   */
  public function getMmsProxyAddress()
  {
    return $this->mmsProxyAddress;
  }
  /**
   * @param int
   */
  public function setMmsProxyPort($mmsProxyPort)
  {
    $this->mmsProxyPort = $mmsProxyPort;
  }
  /**
   * @return int
   */
  public function getMmsProxyPort()
  {
    return $this->mmsProxyPort;
  }
  /**
   * @param string
   */
  public function setMmsc($mmsc)
  {
    $this->mmsc = $mmsc;
  }
  /**
   * @return string
   */
  public function getMmsc()
  {
    return $this->mmsc;
  }
  /**
   * @param int
   */
  public function setMtuV4($mtuV4)
  {
    $this->mtuV4 = $mtuV4;
  }
  /**
   * @return int
   */
  public function getMtuV4()
  {
    return $this->mtuV4;
  }
  /**
   * @param int
   */
  public function setMtuV6($mtuV6)
  {
    $this->mtuV6 = $mtuV6;
  }
  /**
   * @return int
   */
  public function getMtuV6()
  {
    return $this->mtuV6;
  }
  /**
   * @param string
   */
  public function setMvnoType($mvnoType)
  {
    $this->mvnoType = $mvnoType;
  }
  /**
   * @return string
   */
  public function getMvnoType()
  {
    return $this->mvnoType;
  }
  /**
   * @param string[]
   */
  public function setNetworkTypes($networkTypes)
  {
    $this->networkTypes = $networkTypes;
  }
  /**
   * @return string[]
   */
  public function getNetworkTypes()
  {
    return $this->networkTypes;
  }
  /**
   * @param string
   */
  public function setNumericOperatorId($numericOperatorId)
  {
    $this->numericOperatorId = $numericOperatorId;
  }
  /**
   * @return string
   */
  public function getNumericOperatorId()
  {
    return $this->numericOperatorId;
  }
  /**
   * @param string
   */
  public function setPassword($password)
  {
    $this->password = $password;
  }
  /**
   * @return string
   */
  public function getPassword()
  {
    return $this->password;
  }
  /**
   * @param string
   */
  public function setProtocol($protocol)
  {
    $this->protocol = $protocol;
  }
  /**
   * @return string
   */
  public function getProtocol()
  {
    return $this->protocol;
  }
  /**
   * @param string
   */
  public function setProxyAddress($proxyAddress)
  {
    $this->proxyAddress = $proxyAddress;
  }
  /**
   * @return string
   */
  public function getProxyAddress()
  {
    return $this->proxyAddress;
  }
  /**
   * @param int
   */
  public function setProxyPort($proxyPort)
  {
    $this->proxyPort = $proxyPort;
  }
  /**
   * @return int
   */
  public function getProxyPort()
  {
    return $this->proxyPort;
  }
  /**
   * @param string
   */
  public function setRoamingProtocol($roamingProtocol)
  {
    $this->roamingProtocol = $roamingProtocol;
  }
  /**
   * @return string
   */
  public function getRoamingProtocol()
  {
    return $this->roamingProtocol;
  }
  /**
   * @param string
   */
  public function setUsername($username)
  {
    $this->username = $username;
  }
  /**
   * @return string
   */
  public function getUsername()
  {
    return $this->username;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ApnSetting::class, 'Google_Service_AndroidManagement_ApnSetting');
