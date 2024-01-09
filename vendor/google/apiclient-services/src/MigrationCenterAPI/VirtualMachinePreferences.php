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

namespace Google\Service\MigrationCenterAPI;

class VirtualMachinePreferences extends \Google\Model
{
  /**
   * @var string
   */
  public $commitmentPlan;
  protected $computeEnginePreferencesType = ComputeEnginePreferences::class;
  protected $computeEnginePreferencesDataType = '';
  protected $regionPreferencesType = RegionPreferences::class;
  protected $regionPreferencesDataType = '';
  /**
   * @var string
   */
  public $sizingOptimizationStrategy;

  /**
   * @param string
   */
  public function setCommitmentPlan($commitmentPlan)
  {
    $this->commitmentPlan = $commitmentPlan;
  }
  /**
   * @return string
   */
  public function getCommitmentPlan()
  {
    return $this->commitmentPlan;
  }
  /**
   * @param ComputeEnginePreferences
   */
  public function setComputeEnginePreferences(ComputeEnginePreferences $computeEnginePreferences)
  {
    $this->computeEnginePreferences = $computeEnginePreferences;
  }
  /**
   * @return ComputeEnginePreferences
   */
  public function getComputeEnginePreferences()
  {
    return $this->computeEnginePreferences;
  }
  /**
   * @param RegionPreferences
   */
  public function setRegionPreferences(RegionPreferences $regionPreferences)
  {
    $this->regionPreferences = $regionPreferences;
  }
  /**
   * @return RegionPreferences
   */
  public function getRegionPreferences()
  {
    return $this->regionPreferences;
  }
  /**
   * @param string
   */
  public function setSizingOptimizationStrategy($sizingOptimizationStrategy)
  {
    $this->sizingOptimizationStrategy = $sizingOptimizationStrategy;
  }
  /**
   * @return string
   */
  public function getSizingOptimizationStrategy()
  {
    return $this->sizingOptimizationStrategy;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(VirtualMachinePreferences::class, 'Google_Service_MigrationCenterAPI_VirtualMachinePreferences');
