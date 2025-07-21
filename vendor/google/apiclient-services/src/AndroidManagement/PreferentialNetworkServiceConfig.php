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

class PreferentialNetworkServiceConfig extends \Google\Model
{
  /**
   * @var string
   */
  public $fallbackToDefaultConnection;
  /**
   * @var string
   */
  public $nonMatchingNetworks;
  /**
   * @var string
   */
  public $preferentialNetworkId;

  /**
   * @param string
   */
  public function setFallbackToDefaultConnection($fallbackToDefaultConnection)
  {
    $this->fallbackToDefaultConnection = $fallbackToDefaultConnection;
  }
  /**
   * @return string
   */
  public function getFallbackToDefaultConnection()
  {
    return $this->fallbackToDefaultConnection;
  }
  /**
   * @param string
   */
  public function setNonMatchingNetworks($nonMatchingNetworks)
  {
    $this->nonMatchingNetworks = $nonMatchingNetworks;
  }
  /**
   * @return string
   */
  public function getNonMatchingNetworks()
  {
    return $this->nonMatchingNetworks;
  }
  /**
   * @param string
   */
  public function setPreferentialNetworkId($preferentialNetworkId)
  {
    $this->preferentialNetworkId = $preferentialNetworkId;
  }
  /**
   * @return string
   */
  public function getPreferentialNetworkId()
  {
    return $this->preferentialNetworkId;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PreferentialNetworkServiceConfig::class, 'Google_Service_AndroidManagement_PreferentialNetworkServiceConfig');
