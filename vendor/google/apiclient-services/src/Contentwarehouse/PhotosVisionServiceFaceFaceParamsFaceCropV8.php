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

class PhotosVisionServiceFaceFaceParamsFaceCropV8 extends \Google\Model
{
  /**
   * @var float
   */
  public $centerX;
  /**
   * @var float
   */
  public $centerY;
  /**
   * @var float
   */
  public $rotation;
  /**
   * @var float
   */
  public $scale;

  /**
   * @param float
   */
  public function setCenterX($centerX)
  {
    $this->centerX = $centerX;
  }
  /**
   * @return float
   */
  public function getCenterX()
  {
    return $this->centerX;
  }
  /**
   * @param float
   */
  public function setCenterY($centerY)
  {
    $this->centerY = $centerY;
  }
  /**
   * @return float
   */
  public function getCenterY()
  {
    return $this->centerY;
  }
  /**
   * @param float
   */
  public function setRotation($rotation)
  {
    $this->rotation = $rotation;
  }
  /**
   * @return float
   */
  public function getRotation()
  {
    return $this->rotation;
  }
  /**
   * @param float
   */
  public function setScale($scale)
  {
    $this->scale = $scale;
  }
  /**
   * @return float
   */
  public function getScale()
  {
    return $this->scale;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PhotosVisionServiceFaceFaceParamsFaceCropV8::class, 'Google_Service_Contentwarehouse_PhotosVisionServiceFaceFaceParamsFaceCropV8');
