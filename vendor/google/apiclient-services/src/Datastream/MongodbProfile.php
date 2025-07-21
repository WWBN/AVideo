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

namespace Google\Service\Datastream;

class MongodbProfile extends \Google\Collection
{
  protected $collection_key = 'hostAddresses';
  protected $hostAddressesType = HostAddress::class;
  protected $hostAddressesDataType = 'array';
  /**
   * @var string
   */
  public $password;
  /**
   * @var string
   */
  public $replicaSet;
  /**
   * @var string
   */
  public $secretManagerStoredPassword;
  protected $srvConnectionFormatType = SrvConnectionFormat::class;
  protected $srvConnectionFormatDataType = '';
  protected $sslConfigType = MongodbSslConfig::class;
  protected $sslConfigDataType = '';
  protected $standardConnectionFormatType = StandardConnectionFormat::class;
  protected $standardConnectionFormatDataType = '';
  /**
   * @var string
   */
  public $username;

  /**
   * @param HostAddress[]
   */
  public function setHostAddresses($hostAddresses)
  {
    $this->hostAddresses = $hostAddresses;
  }
  /**
   * @return HostAddress[]
   */
  public function getHostAddresses()
  {
    return $this->hostAddresses;
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
  public function setReplicaSet($replicaSet)
  {
    $this->replicaSet = $replicaSet;
  }
  /**
   * @return string
   */
  public function getReplicaSet()
  {
    return $this->replicaSet;
  }
  /**
   * @param string
   */
  public function setSecretManagerStoredPassword($secretManagerStoredPassword)
  {
    $this->secretManagerStoredPassword = $secretManagerStoredPassword;
  }
  /**
   * @return string
   */
  public function getSecretManagerStoredPassword()
  {
    return $this->secretManagerStoredPassword;
  }
  /**
   * @param SrvConnectionFormat
   */
  public function setSrvConnectionFormat(SrvConnectionFormat $srvConnectionFormat)
  {
    $this->srvConnectionFormat = $srvConnectionFormat;
  }
  /**
   * @return SrvConnectionFormat
   */
  public function getSrvConnectionFormat()
  {
    return $this->srvConnectionFormat;
  }
  /**
   * @param MongodbSslConfig
   */
  public function setSslConfig(MongodbSslConfig $sslConfig)
  {
    $this->sslConfig = $sslConfig;
  }
  /**
   * @return MongodbSslConfig
   */
  public function getSslConfig()
  {
    return $this->sslConfig;
  }
  /**
   * @param StandardConnectionFormat
   */
  public function setStandardConnectionFormat(StandardConnectionFormat $standardConnectionFormat)
  {
    $this->standardConnectionFormat = $standardConnectionFormat;
  }
  /**
   * @return StandardConnectionFormat
   */
  public function getStandardConnectionFormat()
  {
    return $this->standardConnectionFormat;
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
class_alias(MongodbProfile::class, 'Google_Service_Datastream_MongodbProfile');
