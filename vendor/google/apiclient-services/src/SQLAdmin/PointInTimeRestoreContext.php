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

namespace Google\Service\SQLAdmin;

class PointInTimeRestoreContext extends \Google\Model
{
  /**
   * @var string
   */
  public $allocatedIpRange;
  /**
   * @var string
   */
  public $datasource;
  /**
   * @var string
   */
  public $pointInTime;
  /**
   * @var string
   */
  public $preferredSecondaryZone;
  /**
   * @var string
   */
  public $preferredZone;
  /**
   * @var string
   */
  public $privateNetwork;
  /**
   * @var string
   */
  public $targetInstance;

  /**
   * @param string
   */
  public function setAllocatedIpRange($allocatedIpRange)
  {
    $this->allocatedIpRange = $allocatedIpRange;
  }
  /**
   * @return string
   */
  public function getAllocatedIpRange()
  {
    return $this->allocatedIpRange;
  }
  /**
   * @param string
   */
  public function setDatasource($datasource)
  {
    $this->datasource = $datasource;
  }
  /**
   * @return string
   */
  public function getDatasource()
  {
    return $this->datasource;
  }
  /**
   * @param string
   */
  public function setPointInTime($pointInTime)
  {
    $this->pointInTime = $pointInTime;
  }
  /**
   * @return string
   */
  public function getPointInTime()
  {
    return $this->pointInTime;
  }
  /**
   * @param string
   */
  public function setPreferredSecondaryZone($preferredSecondaryZone)
  {
    $this->preferredSecondaryZone = $preferredSecondaryZone;
  }
  /**
   * @return string
   */
  public function getPreferredSecondaryZone()
  {
    return $this->preferredSecondaryZone;
  }
  /**
   * @param string
   */
  public function setPreferredZone($preferredZone)
  {
    $this->preferredZone = $preferredZone;
  }
  /**
   * @return string
   */
  public function getPreferredZone()
  {
    return $this->preferredZone;
  }
  /**
   * @param string
   */
  public function setPrivateNetwork($privateNetwork)
  {
    $this->privateNetwork = $privateNetwork;
  }
  /**
   * @return string
   */
  public function getPrivateNetwork()
  {
    return $this->privateNetwork;
  }
  /**
   * @param string
   */
  public function setTargetInstance($targetInstance)
  {
    $this->targetInstance = $targetInstance;
  }
  /**
   * @return string
   */
  public function getTargetInstance()
  {
    return $this->targetInstance;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PointInTimeRestoreContext::class, 'Google_Service_SQLAdmin_PointInTimeRestoreContext');
