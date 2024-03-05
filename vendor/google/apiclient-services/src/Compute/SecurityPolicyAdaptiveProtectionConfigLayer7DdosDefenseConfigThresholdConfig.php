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

namespace Google\Service\Compute;

class SecurityPolicyAdaptiveProtectionConfigLayer7DdosDefenseConfigThresholdConfig extends \Google\Model
{
  /**
   * @var float
   */
  public $autoDeployConfidenceThreshold;
  /**
   * @var int
   */
  public $autoDeployExpirationSec;
  /**
   * @var float
   */
  public $autoDeployImpactedBaselineThreshold;
  /**
   * @var float
   */
  public $autoDeployLoadThreshold;
  /**
   * @var string
   */
  public $name;

  /**
   * @param float
   */
  public function setAutoDeployConfidenceThreshold($autoDeployConfidenceThreshold)
  {
    $this->autoDeployConfidenceThreshold = $autoDeployConfidenceThreshold;
  }
  /**
   * @return float
   */
  public function getAutoDeployConfidenceThreshold()
  {
    return $this->autoDeployConfidenceThreshold;
  }
  /**
   * @param int
   */
  public function setAutoDeployExpirationSec($autoDeployExpirationSec)
  {
    $this->autoDeployExpirationSec = $autoDeployExpirationSec;
  }
  /**
   * @return int
   */
  public function getAutoDeployExpirationSec()
  {
    return $this->autoDeployExpirationSec;
  }
  /**
   * @param float
   */
  public function setAutoDeployImpactedBaselineThreshold($autoDeployImpactedBaselineThreshold)
  {
    $this->autoDeployImpactedBaselineThreshold = $autoDeployImpactedBaselineThreshold;
  }
  /**
   * @return float
   */
  public function getAutoDeployImpactedBaselineThreshold()
  {
    return $this->autoDeployImpactedBaselineThreshold;
  }
  /**
   * @param float
   */
  public function setAutoDeployLoadThreshold($autoDeployLoadThreshold)
  {
    $this->autoDeployLoadThreshold = $autoDeployLoadThreshold;
  }
  /**
   * @return float
   */
  public function getAutoDeployLoadThreshold()
  {
    return $this->autoDeployLoadThreshold;
  }
  /**
   * @param string
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SecurityPolicyAdaptiveProtectionConfigLayer7DdosDefenseConfigThresholdConfig::class, 'Google_Service_Compute_SecurityPolicyAdaptiveProtectionConfigLayer7DdosDefenseConfigThresholdConfig');
