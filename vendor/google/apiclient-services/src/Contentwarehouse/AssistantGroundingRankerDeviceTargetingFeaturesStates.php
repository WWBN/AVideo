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

class AssistantGroundingRankerDeviceTargetingFeaturesStates extends \Google\Model
{
  /**
   * @var string
   */
  public $distance;
  /**
   * @var bool
   */
  public $hasBufferingMediaSession;
  /**
   * @var bool
   */
  public $hasPausedMediaSession;
  /**
   * @var bool
   */
  public $hasPlayingMediaSession;
  /**
   * @var bool
   */
  public $inSameRoomAsHearingDevice;
  /**
   * @var bool
   */
  public $inSameRoomAsLocalDevice;
  /**
   * @var bool
   */
  public $inSameStructureAsHearingDevice;
  /**
   * @var bool
   */
  public $inSameStructureAsLocalDevice;
  /**
   * @var bool
   */
  public $isDocked;
  /**
   * @var bool
   */
  public $isLocal;
  /**
   * @var bool
   */
  public $isLocked;
  /**
   * @var bool
   */
  public $isTethered;
  /**
   * @var string
   */
  public $mediaFocusStateFromHearingDevice;
  /**
   * @var string
   */
  public $mediaFocusStateFromLocalDevice;

  /**
   * @param string
   */
  public function setDistance($distance)
  {
    $this->distance = $distance;
  }
  /**
   * @return string
   */
  public function getDistance()
  {
    return $this->distance;
  }
  /**
   * @param bool
   */
  public function setHasBufferingMediaSession($hasBufferingMediaSession)
  {
    $this->hasBufferingMediaSession = $hasBufferingMediaSession;
  }
  /**
   * @return bool
   */
  public function getHasBufferingMediaSession()
  {
    return $this->hasBufferingMediaSession;
  }
  /**
   * @param bool
   */
  public function setHasPausedMediaSession($hasPausedMediaSession)
  {
    $this->hasPausedMediaSession = $hasPausedMediaSession;
  }
  /**
   * @return bool
   */
  public function getHasPausedMediaSession()
  {
    return $this->hasPausedMediaSession;
  }
  /**
   * @param bool
   */
  public function setHasPlayingMediaSession($hasPlayingMediaSession)
  {
    $this->hasPlayingMediaSession = $hasPlayingMediaSession;
  }
  /**
   * @return bool
   */
  public function getHasPlayingMediaSession()
  {
    return $this->hasPlayingMediaSession;
  }
  /**
   * @param bool
   */
  public function setInSameRoomAsHearingDevice($inSameRoomAsHearingDevice)
  {
    $this->inSameRoomAsHearingDevice = $inSameRoomAsHearingDevice;
  }
  /**
   * @return bool
   */
  public function getInSameRoomAsHearingDevice()
  {
    return $this->inSameRoomAsHearingDevice;
  }
  /**
   * @param bool
   */
  public function setInSameRoomAsLocalDevice($inSameRoomAsLocalDevice)
  {
    $this->inSameRoomAsLocalDevice = $inSameRoomAsLocalDevice;
  }
  /**
   * @return bool
   */
  public function getInSameRoomAsLocalDevice()
  {
    return $this->inSameRoomAsLocalDevice;
  }
  /**
   * @param bool
   */
  public function setInSameStructureAsHearingDevice($inSameStructureAsHearingDevice)
  {
    $this->inSameStructureAsHearingDevice = $inSameStructureAsHearingDevice;
  }
  /**
   * @return bool
   */
  public function getInSameStructureAsHearingDevice()
  {
    return $this->inSameStructureAsHearingDevice;
  }
  /**
   * @param bool
   */
  public function setInSameStructureAsLocalDevice($inSameStructureAsLocalDevice)
  {
    $this->inSameStructureAsLocalDevice = $inSameStructureAsLocalDevice;
  }
  /**
   * @return bool
   */
  public function getInSameStructureAsLocalDevice()
  {
    return $this->inSameStructureAsLocalDevice;
  }
  /**
   * @param bool
   */
  public function setIsDocked($isDocked)
  {
    $this->isDocked = $isDocked;
  }
  /**
   * @return bool
   */
  public function getIsDocked()
  {
    return $this->isDocked;
  }
  /**
   * @param bool
   */
  public function setIsLocal($isLocal)
  {
    $this->isLocal = $isLocal;
  }
  /**
   * @return bool
   */
  public function getIsLocal()
  {
    return $this->isLocal;
  }
  /**
   * @param bool
   */
  public function setIsLocked($isLocked)
  {
    $this->isLocked = $isLocked;
  }
  /**
   * @return bool
   */
  public function getIsLocked()
  {
    return $this->isLocked;
  }
  /**
   * @param bool
   */
  public function setIsTethered($isTethered)
  {
    $this->isTethered = $isTethered;
  }
  /**
   * @return bool
   */
  public function getIsTethered()
  {
    return $this->isTethered;
  }
  /**
   * @param string
   */
  public function setMediaFocusStateFromHearingDevice($mediaFocusStateFromHearingDevice)
  {
    $this->mediaFocusStateFromHearingDevice = $mediaFocusStateFromHearingDevice;
  }
  /**
   * @return string
   */
  public function getMediaFocusStateFromHearingDevice()
  {
    return $this->mediaFocusStateFromHearingDevice;
  }
  /**
   * @param string
   */
  public function setMediaFocusStateFromLocalDevice($mediaFocusStateFromLocalDevice)
  {
    $this->mediaFocusStateFromLocalDevice = $mediaFocusStateFromLocalDevice;
  }
  /**
   * @return string
   */
  public function getMediaFocusStateFromLocalDevice()
  {
    return $this->mediaFocusStateFromLocalDevice;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantGroundingRankerDeviceTargetingFeaturesStates::class, 'Google_Service_Contentwarehouse_AssistantGroundingRankerDeviceTargetingFeaturesStates');
