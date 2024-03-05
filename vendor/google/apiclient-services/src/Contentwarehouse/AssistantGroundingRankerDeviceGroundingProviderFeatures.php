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

namespace Google\Service\Contentwarehouse;

class AssistantGroundingRankerDeviceGroundingProviderFeatures extends \Google\Model
{
  protected $deviceIdType = AssistantApiCoreTypesGovernedDeviceId::class;
  protected $deviceIdDataType = '';
  protected $deviceTargetingFeaturesType = AssistantGroundingRankerDeviceTargetingFeatures::class;
  protected $deviceTargetingFeaturesDataType = '';
  protected $deviceTargetingLabelsType = AssistantGroundingRankerDeviceTargetingLabels::class;
  protected $deviceTargetingLabelsDataType = '';
  protected $surfaceIdentityType = AssistantApiCoreTypesGovernedSurfaceIdentity::class;
  protected $surfaceIdentityDataType = '';

  /**
   * @param AssistantApiCoreTypesGovernedDeviceId
   */
  public function setDeviceId(AssistantApiCoreTypesGovernedDeviceId $deviceId)
  {
    $this->deviceId = $deviceId;
  }
  /**
   * @return AssistantApiCoreTypesGovernedDeviceId
   */
  public function getDeviceId()
  {
    return $this->deviceId;
  }
  /**
   * @param AssistantGroundingRankerDeviceTargetingFeatures
   */
  public function setDeviceTargetingFeatures(AssistantGroundingRankerDeviceTargetingFeatures $deviceTargetingFeatures)
  {
    $this->deviceTargetingFeatures = $deviceTargetingFeatures;
  }
  /**
   * @return AssistantGroundingRankerDeviceTargetingFeatures
   */
  public function getDeviceTargetingFeatures()
  {
    return $this->deviceTargetingFeatures;
  }
  /**
   * @param AssistantGroundingRankerDeviceTargetingLabels
   */
  public function setDeviceTargetingLabels(AssistantGroundingRankerDeviceTargetingLabels $deviceTargetingLabels)
  {
    $this->deviceTargetingLabels = $deviceTargetingLabels;
  }
  /**
   * @return AssistantGroundingRankerDeviceTargetingLabels
   */
  public function getDeviceTargetingLabels()
  {
    return $this->deviceTargetingLabels;
  }
  /**
   * @param AssistantApiCoreTypesGovernedSurfaceIdentity
   */
  public function setSurfaceIdentity(AssistantApiCoreTypesGovernedSurfaceIdentity $surfaceIdentity)
  {
    $this->surfaceIdentity = $surfaceIdentity;
  }
  /**
   * @return AssistantApiCoreTypesGovernedSurfaceIdentity
   */
  public function getSurfaceIdentity()
  {
    return $this->surfaceIdentity;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantGroundingRankerDeviceGroundingProviderFeatures::class, 'Google_Service_Contentwarehouse_AssistantGroundingRankerDeviceGroundingProviderFeatures');
