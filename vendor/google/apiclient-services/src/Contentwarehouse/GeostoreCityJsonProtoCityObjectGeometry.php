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

class GeostoreCityJsonProtoCityObjectGeometry extends \Google\Model
{
  /**
   * @var string
   */
  public $lod;
  protected $multipointType = GeostoreCityJsonProtoCityObjectGeometryMultiPoint::class;
  protected $multipointDataType = '';
  protected $multisurfaceType = GeostoreCityJsonProtoCityObjectGeometryMultiSurface::class;
  protected $multisurfaceDataType = '';
  protected $solidType = GeostoreCityJsonProtoCityObjectGeometrySolid::class;
  protected $solidDataType = '';

  /**
   * @param string
   */
  public function setLod($lod)
  {
    $this->lod = $lod;
  }
  /**
   * @return string
   */
  public function getLod()
  {
    return $this->lod;
  }
  /**
   * @param GeostoreCityJsonProtoCityObjectGeometryMultiPoint
   */
  public function setMultipoint(GeostoreCityJsonProtoCityObjectGeometryMultiPoint $multipoint)
  {
    $this->multipoint = $multipoint;
  }
  /**
   * @return GeostoreCityJsonProtoCityObjectGeometryMultiPoint
   */
  public function getMultipoint()
  {
    return $this->multipoint;
  }
  /**
   * @param GeostoreCityJsonProtoCityObjectGeometryMultiSurface
   */
  public function setMultisurface(GeostoreCityJsonProtoCityObjectGeometryMultiSurface $multisurface)
  {
    $this->multisurface = $multisurface;
  }
  /**
   * @return GeostoreCityJsonProtoCityObjectGeometryMultiSurface
   */
  public function getMultisurface()
  {
    return $this->multisurface;
  }
  /**
   * @param GeostoreCityJsonProtoCityObjectGeometrySolid
   */
  public function setSolid(GeostoreCityJsonProtoCityObjectGeometrySolid $solid)
  {
    $this->solid = $solid;
  }
  /**
   * @return GeostoreCityJsonProtoCityObjectGeometrySolid
   */
  public function getSolid()
  {
    return $this->solid;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GeostoreCityJsonProtoCityObjectGeometry::class, 'Google_Service_Contentwarehouse_GeostoreCityJsonProtoCityObjectGeometry');
