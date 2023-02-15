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

class SocialGraphApiProtoPhotoOverlay extends \Google\Model
{
  protected $relativePositionType = SocialGraphApiProtoRelativePosition::class;
  protected $relativePositionDataType = '';
  public $relativePosition;
  protected $relativeScaleType = SocialGraphApiProtoRelativeScale::class;
  protected $relativeScaleDataType = '';
  public $relativeScale;

  /**
   * @param SocialGraphApiProtoRelativePosition
   */
  public function setRelativePosition(SocialGraphApiProtoRelativePosition $relativePosition)
  {
    $this->relativePosition = $relativePosition;
  }
  /**
   * @return SocialGraphApiProtoRelativePosition
   */
  public function getRelativePosition()
  {
    return $this->relativePosition;
  }
  /**
   * @param SocialGraphApiProtoRelativeScale
   */
  public function setRelativeScale(SocialGraphApiProtoRelativeScale $relativeScale)
  {
    $this->relativeScale = $relativeScale;
  }
  /**
   * @return SocialGraphApiProtoRelativeScale
   */
  public function getRelativeScale()
  {
    return $this->relativeScale;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SocialGraphApiProtoPhotoOverlay::class, 'Google_Service_Contentwarehouse_SocialGraphApiProtoPhotoOverlay');
