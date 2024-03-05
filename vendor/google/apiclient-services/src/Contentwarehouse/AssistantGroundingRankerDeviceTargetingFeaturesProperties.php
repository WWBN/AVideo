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

class AssistantGroundingRankerDeviceTargetingFeaturesProperties extends \Google\Model
{
  /**
   * @var string
   */
  public $deviceModelId;
  /**
   * @var bool
   */
  public $isOwnedBySpeaker;
  /**
   * @var string
   */
  public $surfaceType;

  /**
   * @param string
   */
  public function setDeviceModelId($deviceModelId)
  {
    $this->deviceModelId = $deviceModelId;
  }
  /**
   * @return string
   */
  public function getDeviceModelId()
  {
    return $this->deviceModelId;
  }
  /**
   * @param bool
   */
  public function setIsOwnedBySpeaker($isOwnedBySpeaker)
  {
    $this->isOwnedBySpeaker = $isOwnedBySpeaker;
  }
  /**
   * @return bool
   */
  public function getIsOwnedBySpeaker()
  {
    return $this->isOwnedBySpeaker;
  }
  /**
   * @param string
   */
  public function setSurfaceType($surfaceType)
  {
    $this->surfaceType = $surfaceType;
  }
  /**
   * @return string
   */
  public function getSurfaceType()
  {
    return $this->surfaceType;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantGroundingRankerDeviceTargetingFeaturesProperties::class, 'Google_Service_Contentwarehouse_AssistantGroundingRankerDeviceTargetingFeaturesProperties');
