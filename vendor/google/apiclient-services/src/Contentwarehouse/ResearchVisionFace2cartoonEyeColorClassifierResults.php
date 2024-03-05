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

class ResearchVisionFace2cartoonEyeColorClassifierResults extends \Google\Model
{
  /**
   * @var string
   */
  public $color;
  /**
   * @var float
   */
  public $confidence;

  /**
   * @param string
   */
  public function setColor($color)
  {
    $this->color = $color;
  }
  /**
   * @return string
   */
  public function getColor()
  {
    return $this->color;
  }
  /**
   * @param float
   */
  public function setConfidence($confidence)
  {
    $this->confidence = $confidence;
  }
  /**
   * @return float
   */
  public function getConfidence()
  {
    return $this->confidence;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ResearchVisionFace2cartoonEyeColorClassifierResults::class, 'Google_Service_Contentwarehouse_ResearchVisionFace2cartoonEyeColorClassifierResults');
