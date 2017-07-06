<?php
/*
 * Copyright 2016 Google Inc.
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

class Google_Service_Slides_Shadow extends Google_Model
{
  public $alignment;
  public $alpha;
  protected $blurRadiusType = 'Google_Service_Slides_Dimension';
  protected $blurRadiusDataType = '';
  protected $colorType = 'Google_Service_Slides_OpaqueColor';
  protected $colorDataType = '';
  public $propertyState;
  public $rotateWithShape;
  protected $transformType = 'Google_Service_Slides_AffineTransform';
  protected $transformDataType = '';
  public $type;

  public function setAlignment($alignment)
  {
    $this->alignment = $alignment;
  }
  public function getAlignment()
  {
    return $this->alignment;
  }
  public function setAlpha($alpha)
  {
    $this->alpha = $alpha;
  }
  public function getAlpha()
  {
    return $this->alpha;
  }
  public function setBlurRadius(Google_Service_Slides_Dimension $blurRadius)
  {
    $this->blurRadius = $blurRadius;
  }
  public function getBlurRadius()
  {
    return $this->blurRadius;
  }
  public function setColor(Google_Service_Slides_OpaqueColor $color)
  {
    $this->color = $color;
  }
  public function getColor()
  {
    return $this->color;
  }
  public function setPropertyState($propertyState)
  {
    $this->propertyState = $propertyState;
  }
  public function getPropertyState()
  {
    return $this->propertyState;
  }
  public function setRotateWithShape($rotateWithShape)
  {
    $this->rotateWithShape = $rotateWithShape;
  }
  public function getRotateWithShape()
  {
    return $this->rotateWithShape;
  }
  public function setTransform(Google_Service_Slides_AffineTransform $transform)
  {
    $this->transform = $transform;
  }
  public function getTransform()
  {
    return $this->transform;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
}
