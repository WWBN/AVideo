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

class ReportSummaryGroupPreferenceSetFinding extends \Google\Model
{
  /**
   * @var string
   */
  public $description;
  /**
   * @var string
   */
  public $displayName;
  protected $machineFindingType = ReportSummaryMachineFinding::class;
  protected $machineFindingDataType = '';
  protected $machinePreferencesType = VirtualMachinePreferences::class;
  protected $machinePreferencesDataType = '';
  protected $monthlyCostComputeType = Money::class;
  protected $monthlyCostComputeDataType = '';
  protected $monthlyCostNetworkEgressType = Money::class;
  protected $monthlyCostNetworkEgressDataType = '';
  protected $monthlyCostOsLicenseType = Money::class;
  protected $monthlyCostOsLicenseDataType = '';
  protected $monthlyCostOtherType = Money::class;
  protected $monthlyCostOtherDataType = '';
  protected $monthlyCostStorageType = Money::class;
  protected $monthlyCostStorageDataType = '';
  protected $monthlyCostTotalType = Money::class;
  protected $monthlyCostTotalDataType = '';
  /**
   * @var string
   */
  public $preferredRegion;
  /**
   * @var string
   */
  public $pricingTrack;
  /**
   * @var string
   */
  public $topPriority;

  /**
   * @param string
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }
  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
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
   * @param ReportSummaryMachineFinding
   */
  public function setMachineFinding(ReportSummaryMachineFinding $machineFinding)
  {
    $this->machineFinding = $machineFinding;
  }
  /**
   * @return ReportSummaryMachineFinding
   */
  public function getMachineFinding()
  {
    return $this->machineFinding;
  }
  /**
   * @param VirtualMachinePreferences
   */
  public function setMachinePreferences(VirtualMachinePreferences $machinePreferences)
  {
    $this->machinePreferences = $machinePreferences;
  }
  /**
   * @return VirtualMachinePreferences
   */
  public function getMachinePreferences()
  {
    return $this->machinePreferences;
  }
  /**
   * @param Money
   */
  public function setMonthlyCostCompute(Money $monthlyCostCompute)
  {
    $this->monthlyCostCompute = $monthlyCostCompute;
  }
  /**
   * @return Money
   */
  public function getMonthlyCostCompute()
  {
    return $this->monthlyCostCompute;
  }
  /**
   * @param Money
   */
  public function setMonthlyCostNetworkEgress(Money $monthlyCostNetworkEgress)
  {
    $this->monthlyCostNetworkEgress = $monthlyCostNetworkEgress;
  }
  /**
   * @return Money
   */
  public function getMonthlyCostNetworkEgress()
  {
    return $this->monthlyCostNetworkEgress;
  }
  /**
   * @param Money
   */
  public function setMonthlyCostOsLicense(Money $monthlyCostOsLicense)
  {
    $this->monthlyCostOsLicense = $monthlyCostOsLicense;
  }
  /**
   * @return Money
   */
  public function getMonthlyCostOsLicense()
  {
    return $this->monthlyCostOsLicense;
  }
  /**
   * @param Money
   */
  public function setMonthlyCostOther(Money $monthlyCostOther)
  {
    $this->monthlyCostOther = $monthlyCostOther;
  }
  /**
   * @return Money
   */
  public function getMonthlyCostOther()
  {
    return $this->monthlyCostOther;
  }
  /**
   * @param Money
   */
  public function setMonthlyCostStorage(Money $monthlyCostStorage)
  {
    $this->monthlyCostStorage = $monthlyCostStorage;
  }
  /**
   * @return Money
   */
  public function getMonthlyCostStorage()
  {
    return $this->monthlyCostStorage;
  }
  /**
   * @param Money
   */
  public function setMonthlyCostTotal(Money $monthlyCostTotal)
  {
    $this->monthlyCostTotal = $monthlyCostTotal;
  }
  /**
   * @return Money
   */
  public function getMonthlyCostTotal()
  {
    return $this->monthlyCostTotal;
  }
  /**
   * @param string
   */
  public function setPreferredRegion($preferredRegion)
  {
    $this->preferredRegion = $preferredRegion;
  }
  /**
   * @return string
   */
  public function getPreferredRegion()
  {
    return $this->preferredRegion;
  }
  /**
   * @param string
   */
  public function setPricingTrack($pricingTrack)
  {
    $this->pricingTrack = $pricingTrack;
  }
  /**
   * @return string
   */
  public function getPricingTrack()
  {
    return $this->pricingTrack;
  }
  /**
   * @param string
   */
  public function setTopPriority($topPriority)
  {
    $this->topPriority = $topPriority;
  }
  /**
   * @return string
   */
  public function getTopPriority()
  {
    return $this->topPriority;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ReportSummaryGroupPreferenceSetFinding::class, 'Google_Service_MigrationCenterAPI_ReportSummaryGroupPreferenceSetFinding');
