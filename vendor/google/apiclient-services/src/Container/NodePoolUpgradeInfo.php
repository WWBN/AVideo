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

namespace Google\Service\Container;

class NodePoolUpgradeInfo extends \Google\Collection
{
  protected $collection_key = 'upgradeDetails';
  /**
   * @var string[]
   */
  public $autoUpgradeStatus;
  /**
   * @var string
   */
  public $endOfExtendedSupportTimestamp;
  /**
   * @var string
   */
  public $endOfStandardSupportTimestamp;
  /**
   * @var string
   */
  public $minorTargetVersion;
  /**
   * @var string
   */
  public $patchTargetVersion;
  /**
   * @var string[]
   */
  public $pausedReason;
  protected $upgradeDetailsType = UpgradeDetails::class;
  protected $upgradeDetailsDataType = 'array';

  /**
   * @param string[]
   */
  public function setAutoUpgradeStatus($autoUpgradeStatus)
  {
    $this->autoUpgradeStatus = $autoUpgradeStatus;
  }
  /**
   * @return string[]
   */
  public function getAutoUpgradeStatus()
  {
    return $this->autoUpgradeStatus;
  }
  /**
   * @param string
   */
  public function setEndOfExtendedSupportTimestamp($endOfExtendedSupportTimestamp)
  {
    $this->endOfExtendedSupportTimestamp = $endOfExtendedSupportTimestamp;
  }
  /**
   * @return string
   */
  public function getEndOfExtendedSupportTimestamp()
  {
    return $this->endOfExtendedSupportTimestamp;
  }
  /**
   * @param string
   */
  public function setEndOfStandardSupportTimestamp($endOfStandardSupportTimestamp)
  {
    $this->endOfStandardSupportTimestamp = $endOfStandardSupportTimestamp;
  }
  /**
   * @return string
   */
  public function getEndOfStandardSupportTimestamp()
  {
    return $this->endOfStandardSupportTimestamp;
  }
  /**
   * @param string
   */
  public function setMinorTargetVersion($minorTargetVersion)
  {
    $this->minorTargetVersion = $minorTargetVersion;
  }
  /**
   * @return string
   */
  public function getMinorTargetVersion()
  {
    return $this->minorTargetVersion;
  }
  /**
   * @param string
   */
  public function setPatchTargetVersion($patchTargetVersion)
  {
    $this->patchTargetVersion = $patchTargetVersion;
  }
  /**
   * @return string
   */
  public function getPatchTargetVersion()
  {
    return $this->patchTargetVersion;
  }
  /**
   * @param string[]
   */
  public function setPausedReason($pausedReason)
  {
    $this->pausedReason = $pausedReason;
  }
  /**
   * @return string[]
   */
  public function getPausedReason()
  {
    return $this->pausedReason;
  }
  /**
   * @param UpgradeDetails[]
   */
  public function setUpgradeDetails($upgradeDetails)
  {
    $this->upgradeDetails = $upgradeDetails;
  }
  /**
   * @return UpgradeDetails[]
   */
  public function getUpgradeDetails()
  {
    return $this->upgradeDetails;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(NodePoolUpgradeInfo::class, 'Google_Service_Container_NodePoolUpgradeInfo');
