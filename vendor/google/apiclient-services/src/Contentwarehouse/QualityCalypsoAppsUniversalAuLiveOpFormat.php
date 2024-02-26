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

class QualityCalypsoAppsUniversalAuLiveOpFormat extends \Google\Model
{
  /**
   * @var string
   */
  public $deeplink;
  /**
   * @var string
   */
  public $description;
  /**
   * @var string
   */
  public $eyebrow;
  /**
   * @var string
   */
  public $imageUrl;
  /**
   * @var string
   */
  public $originalImageUrl;
  /**
   * @var string
   */
  public $squareImageUrl;
  /**
   * @var string
   */
  public $status;
  /**
   * @var string
   */
  public $title;
  /**
   * @var string
   */
  public $videoId;
  /**
   * @var string
   */
  public $videoUrl;

  /**
   * @param string
   */
  public function setDeeplink($deeplink)
  {
    $this->deeplink = $deeplink;
  }
  /**
   * @return string
   */
  public function getDeeplink()
  {
    return $this->deeplink;
  }
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
  public function setEyebrow($eyebrow)
  {
    $this->eyebrow = $eyebrow;
  }
  /**
   * @return string
   */
  public function getEyebrow()
  {
    return $this->eyebrow;
  }
  /**
   * @param string
   */
  public function setImageUrl($imageUrl)
  {
    $this->imageUrl = $imageUrl;
  }
  /**
   * @return string
   */
  public function getImageUrl()
  {
    return $this->imageUrl;
  }
  /**
   * @param string
   */
  public function setOriginalImageUrl($originalImageUrl)
  {
    $this->originalImageUrl = $originalImageUrl;
  }
  /**
   * @return string
   */
  public function getOriginalImageUrl()
  {
    return $this->originalImageUrl;
  }
  /**
   * @param string
   */
  public function setSquareImageUrl($squareImageUrl)
  {
    $this->squareImageUrl = $squareImageUrl;
  }
  /**
   * @return string
   */
  public function getSquareImageUrl()
  {
    return $this->squareImageUrl;
  }
  /**
   * @param string
   */
  public function setStatus($status)
  {
    $this->status = $status;
  }
  /**
   * @return string
   */
  public function getStatus()
  {
    return $this->status;
  }
  /**
   * @param string
   */
  public function setTitle($title)
  {
    $this->title = $title;
  }
  /**
   * @return string
   */
  public function getTitle()
  {
    return $this->title;
  }
  /**
   * @param string
   */
  public function setVideoId($videoId)
  {
    $this->videoId = $videoId;
  }
  /**
   * @return string
   */
  public function getVideoId()
  {
    return $this->videoId;
  }
  /**
   * @param string
   */
  public function setVideoUrl($videoUrl)
  {
    $this->videoUrl = $videoUrl;
  }
  /**
   * @return string
   */
  public function getVideoUrl()
  {
    return $this->videoUrl;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(QualityCalypsoAppsUniversalAuLiveOpFormat::class, 'Google_Service_Contentwarehouse_QualityCalypsoAppsUniversalAuLiveOpFormat');
