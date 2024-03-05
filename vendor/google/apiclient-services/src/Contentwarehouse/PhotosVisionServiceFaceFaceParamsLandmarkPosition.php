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

class PhotosVisionServiceFaceFaceParamsLandmarkPosition extends \Google\Model
{
  /**
   * @var string
   */
  public $landmark;
  /**
   * @var int
   */
  public $x;
  /**
   * @var float
   */
  public $xF;
  /**
   * @var int
   */
  public $y;
  /**
   * @var float
   */
  public $yF;
  /**
   * @var float
   */
  public $z;

  /**
   * @param string
   */
  public function setLandmark($landmark)
  {
    $this->landmark = $landmark;
  }
  /**
   * @return string
   */
  public function getLandmark()
  {
    return $this->landmark;
  }
  /**
   * @param int
   */
  public function setX($x)
  {
    $this->x = $x;
  }
  /**
   * @return int
   */
  public function getX()
  {
    return $this->x;
  }
  /**
   * @param float
   */
  public function setXF($xF)
  {
    $this->xF = $xF;
  }
  /**
   * @return float
   */
  public function getXF()
  {
    return $this->xF;
  }
  /**
   * @param int
   */
  public function setY($y)
  {
    $this->y = $y;
  }
  /**
   * @return int
   */
  public function getY()
  {
    return $this->y;
  }
  /**
   * @param float
   */
  public function setYF($yF)
  {
    $this->yF = $yF;
  }
  /**
   * @return float
   */
  public function getYF()
  {
    return $this->yF;
  }
  /**
   * @param float
   */
  public function setZ($z)
  {
    $this->z = $z;
  }
  /**
   * @return float
   */
  public function getZ()
  {
    return $this->z;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PhotosVisionServiceFaceFaceParamsLandmarkPosition::class, 'Google_Service_Contentwarehouse_PhotosVisionServiceFaceFaceParamsLandmarkPosition');
