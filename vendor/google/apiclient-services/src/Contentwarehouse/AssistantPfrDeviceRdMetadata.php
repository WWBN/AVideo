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

class AssistantPfrDeviceRdMetadata extends \Google\Collection
{
  protected $collection_key = 'deviceTypes';
  /**
   * @var string
   */
  public $deviceName;
  /**
   * @var string[]
   */
  public $deviceTypes;
  /**
   * @var float
   */
  public $effectiveArgSpanLength;
  /**
   * @var bool
   */
  public $hasAmbiguousResolutions;
  /**
   * @var bool
   */
  public $hasResolvedDeviceId;
  /**
   * @var string
   */
  public $roomName;

  /**
   * @param string
   */
  public function setDeviceName($deviceName)
  {
    $this->deviceName = $deviceName;
  }
  /**
   * @return string
   */
  public function getDeviceName()
  {
    return $this->deviceName;
  }
  /**
   * @param string[]
   */
  public function setDeviceTypes($deviceTypes)
  {
    $this->deviceTypes = $deviceTypes;
  }
  /**
   * @return string[]
   */
  public function getDeviceTypes()
  {
    return $this->deviceTypes;
  }
  /**
   * @param float
   */
  public function setEffectiveArgSpanLength($effectiveArgSpanLength)
  {
    $this->effectiveArgSpanLength = $effectiveArgSpanLength;
  }
  /**
   * @return float
   */
  public function getEffectiveArgSpanLength()
  {
    return $this->effectiveArgSpanLength;
  }
  /**
   * @param bool
   */
  public function setHasAmbiguousResolutions($hasAmbiguousResolutions)
  {
    $this->hasAmbiguousResolutions = $hasAmbiguousResolutions;
  }
  /**
   * @return bool
   */
  public function getHasAmbiguousResolutions()
  {
    return $this->hasAmbiguousResolutions;
  }
  /**
   * @param bool
   */
  public function setHasResolvedDeviceId($hasResolvedDeviceId)
  {
    $this->hasResolvedDeviceId = $hasResolvedDeviceId;
  }
  /**
   * @return bool
   */
  public function getHasResolvedDeviceId()
  {
    return $this->hasResolvedDeviceId;
  }
  /**
   * @param string
   */
  public function setRoomName($roomName)
  {
    $this->roomName = $roomName;
  }
  /**
   * @return string
   */
  public function getRoomName()
  {
    return $this->roomName;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantPfrDeviceRdMetadata::class, 'Google_Service_Contentwarehouse_AssistantPfrDeviceRdMetadata');
