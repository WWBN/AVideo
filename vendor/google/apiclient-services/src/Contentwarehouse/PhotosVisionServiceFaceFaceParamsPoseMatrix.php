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

class PhotosVisionServiceFaceFaceParamsPoseMatrix extends \Google\Model
{
  /**
   * @var float
   */
  public $xx;
  /**
   * @var float
   */
  public $xy;
  /**
   * @var float
   */
  public $xz;
  /**
   * @var float
   */
  public $yx;
  /**
   * @var float
   */
  public $yy;
  /**
   * @var float
   */
  public $yz;
  /**
   * @var float
   */
  public $zx;
  /**
   * @var float
   */
  public $zy;
  /**
   * @var float
   */
  public $zz;

  /**
   * @param float
   */
  public function setXx($xx)
  {
    $this->xx = $xx;
  }
  /**
   * @return float
   */
  public function getXx()
  {
    return $this->xx;
  }
  /**
   * @param float
   */
  public function setXy($xy)
  {
    $this->xy = $xy;
  }
  /**
   * @return float
   */
  public function getXy()
  {
    return $this->xy;
  }
  /**
   * @param float
   */
  public function setXz($xz)
  {
    $this->xz = $xz;
  }
  /**
   * @return float
   */
  public function getXz()
  {
    return $this->xz;
  }
  /**
   * @param float
   */
  public function setYx($yx)
  {
    $this->yx = $yx;
  }
  /**
   * @return float
   */
  public function getYx()
  {
    return $this->yx;
  }
  /**
   * @param float
   */
  public function setYy($yy)
  {
    $this->yy = $yy;
  }
  /**
   * @return float
   */
  public function getYy()
  {
    return $this->yy;
  }
  /**
   * @param float
   */
  public function setYz($yz)
  {
    $this->yz = $yz;
  }
  /**
   * @return float
   */
  public function getYz()
  {
    return $this->yz;
  }
  /**
   * @param float
   */
  public function setZx($zx)
  {
    $this->zx = $zx;
  }
  /**
   * @return float
   */
  public function getZx()
  {
    return $this->zx;
  }
  /**
   * @param float
   */
  public function setZy($zy)
  {
    $this->zy = $zy;
  }
  /**
   * @return float
   */
  public function getZy()
  {
    return $this->zy;
  }
  /**
   * @param float
   */
  public function setZz($zz)
  {
    $this->zz = $zz;
  }
  /**
   * @return float
   */
  public function getZz()
  {
    return $this->zz;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PhotosVisionServiceFaceFaceParamsPoseMatrix::class, 'Google_Service_Contentwarehouse_PhotosVisionServiceFaceFaceParamsPoseMatrix');
