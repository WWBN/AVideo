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

namespace Google\Service\NetworkManagement;

class DirectVpcEgressConnectionInfo extends \Google\Model
{
  /**
   * @var string
   */
  public $networkUri;
  /**
   * @var string
   */
  public $region;
  /**
   * @var string
   */
  public $selectedIpAddress;
  /**
   * @var string
   */
  public $selectedIpRange;
  /**
   * @var string
   */
  public $subnetworkUri;

  /**
   * @param string
   */
  public function setNetworkUri($networkUri)
  {
    $this->networkUri = $networkUri;
  }
  /**
   * @return string
   */
  public function getNetworkUri()
  {
    return $this->networkUri;
  }
  /**
   * @param string
   */
  public function setRegion($region)
  {
    $this->region = $region;
  }
  /**
   * @return string
   */
  public function getRegion()
  {
    return $this->region;
  }
  /**
   * @param string
   */
  public function setSelectedIpAddress($selectedIpAddress)
  {
    $this->selectedIpAddress = $selectedIpAddress;
  }
  /**
   * @return string
   */
  public function getSelectedIpAddress()
  {
    return $this->selectedIpAddress;
  }
  /**
   * @param string
   */
  public function setSelectedIpRange($selectedIpRange)
  {
    $this->selectedIpRange = $selectedIpRange;
  }
  /**
   * @return string
   */
  public function getSelectedIpRange()
  {
    return $this->selectedIpRange;
  }
  /**
   * @param string
   */
  public function setSubnetworkUri($subnetworkUri)
  {
    $this->subnetworkUri = $subnetworkUri;
  }
  /**
   * @return string
   */
  public function getSubnetworkUri()
  {
    return $this->subnetworkUri;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(DirectVpcEgressConnectionInfo::class, 'Google_Service_NetworkManagement_DirectVpcEgressConnectionInfo');
