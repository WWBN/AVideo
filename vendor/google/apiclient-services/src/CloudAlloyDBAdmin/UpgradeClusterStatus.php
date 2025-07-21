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

namespace Google\Service\CloudAlloyDBAdmin;

class UpgradeClusterStatus extends \Google\Collection
{
  protected $collection_key = 'stages';
  /**
   * @var bool
   */
  public $cancellable;
  /**
   * @var string
   */
  public $sourceVersion;
  protected $stagesType = StageStatus::class;
  protected $stagesDataType = 'array';
  /**
   * @var string
   */
  public $state;
  /**
   * @var string
   */
  public $targetVersion;

  /**
   * @param bool
   */
  public function setCancellable($cancellable)
  {
    $this->cancellable = $cancellable;
  }
  /**
   * @return bool
   */
  public function getCancellable()
  {
    return $this->cancellable;
  }
  /**
   * @param string
   */
  public function setSourceVersion($sourceVersion)
  {
    $this->sourceVersion = $sourceVersion;
  }
  /**
   * @return string
   */
  public function getSourceVersion()
  {
    return $this->sourceVersion;
  }
  /**
   * @param StageStatus[]
   */
  public function setStages($stages)
  {
    $this->stages = $stages;
  }
  /**
   * @return StageStatus[]
   */
  public function getStages()
  {
    return $this->stages;
  }
  /**
   * @param string
   */
  public function setState($state)
  {
    $this->state = $state;
  }
  /**
   * @return string
   */
  public function getState()
  {
    return $this->state;
  }
  /**
   * @param string
   */
  public function setTargetVersion($targetVersion)
  {
    $this->targetVersion = $targetVersion;
  }
  /**
   * @return string
   */
  public function getTargetVersion()
  {
    return $this->targetVersion;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(UpgradeClusterStatus::class, 'Google_Service_CloudAlloyDBAdmin_UpgradeClusterStatus');
