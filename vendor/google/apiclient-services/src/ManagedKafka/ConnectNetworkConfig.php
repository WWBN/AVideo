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

namespace Google\Service\ManagedKafka;

class ConnectNetworkConfig extends \Google\Collection
{
  protected $collection_key = 'dnsDomainNames';
  /**
   * @var string[]
   */
  public $additionalSubnets;
  /**
   * @var string[]
   */
  public $dnsDomainNames;
  /**
   * @var string
   */
  public $primarySubnet;

  /**
   * @param string[]
   */
  public function setAdditionalSubnets($additionalSubnets)
  {
    $this->additionalSubnets = $additionalSubnets;
  }
  /**
   * @return string[]
   */
  public function getAdditionalSubnets()
  {
    return $this->additionalSubnets;
  }
  /**
   * @param string[]
   */
  public function setDnsDomainNames($dnsDomainNames)
  {
    $this->dnsDomainNames = $dnsDomainNames;
  }
  /**
   * @return string[]
   */
  public function getDnsDomainNames()
  {
    return $this->dnsDomainNames;
  }
  /**
   * @param string
   */
  public function setPrimarySubnet($primarySubnet)
  {
    $this->primarySubnet = $primarySubnet;
  }
  /**
   * @return string
   */
  public function getPrimarySubnet()
  {
    return $this->primarySubnet;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ConnectNetworkConfig::class, 'Google_Service_ManagedKafka_ConnectNetworkConfig');
