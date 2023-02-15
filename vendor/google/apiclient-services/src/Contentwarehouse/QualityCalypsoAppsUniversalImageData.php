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

class QualityCalypsoAppsUniversalImageData extends \Google\Collection
{
  protected $collection_key = 'screenshot';
  protected $enhancedImageType = QualityCalypsoAppsUniversalImage::class;
  protected $enhancedImageDataType = '';
  public $enhancedImage;
  protected $featureGraphicType = QualityCalypsoAppsUniversalImage::class;
  protected $featureGraphicDataType = '';
  public $featureGraphic;
  protected $screenshotType = QualityCalypsoAppsUniversalImage::class;
  protected $screenshotDataType = 'array';
  public $screenshot;

  /**
   * @param QualityCalypsoAppsUniversalImage
   */
  public function setEnhancedImage(QualityCalypsoAppsUniversalImage $enhancedImage)
  {
    $this->enhancedImage = $enhancedImage;
  }
  /**
   * @return QualityCalypsoAppsUniversalImage
   */
  public function getEnhancedImage()
  {
    return $this->enhancedImage;
  }
  /**
   * @param QualityCalypsoAppsUniversalImage
   */
  public function setFeatureGraphic(QualityCalypsoAppsUniversalImage $featureGraphic)
  {
    $this->featureGraphic = $featureGraphic;
  }
  /**
   * @return QualityCalypsoAppsUniversalImage
   */
  public function getFeatureGraphic()
  {
    return $this->featureGraphic;
  }
  /**
   * @param QualityCalypsoAppsUniversalImage[]
   */
  public function setScreenshot($screenshot)
  {
    $this->screenshot = $screenshot;
  }
  /**
   * @return QualityCalypsoAppsUniversalImage[]
   */
  public function getScreenshot()
  {
    return $this->screenshot;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(QualityCalypsoAppsUniversalImageData::class, 'Google_Service_Contentwarehouse_QualityCalypsoAppsUniversalImageData');
