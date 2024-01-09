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

class SocialGraphApiProtoDecorationOverlay extends \Google\Model
{
  protected $overlayType = SocialGraphApiProtoPhotoOverlay::class;
  protected $overlayDataType = '';
  /**
   * @var string
   */
  public $sibsId;

  /**
   * @param SocialGraphApiProtoPhotoOverlay
   */
  public function setOverlay(SocialGraphApiProtoPhotoOverlay $overlay)
  {
    $this->overlay = $overlay;
  }
  /**
   * @return SocialGraphApiProtoPhotoOverlay
   */
  public function getOverlay()
  {
    return $this->overlay;
  }
  /**
   * @param string
   */
  public function setSibsId($sibsId)
  {
    $this->sibsId = $sibsId;
  }
  /**
   * @return string
   */
  public function getSibsId()
  {
    return $this->sibsId;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SocialGraphApiProtoDecorationOverlay::class, 'Google_Service_Contentwarehouse_SocialGraphApiProtoDecorationOverlay');
