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

namespace Google\Service\SA360;

class GoogleAdsSearchads360V0CommonSearchAds360ResponsiveSearchAdInfo extends \Google\Model
{
  /**
   * @var string
   */
  public $adTrackingId;
  /**
   * @var string
   */
  public $path1;
  /**
   * @var string
   */
  public $path2;

  /**
   * @param string
   */
  public function setAdTrackingId($adTrackingId)
  {
    $this->adTrackingId = $adTrackingId;
  }
  /**
   * @return string
   */
  public function getAdTrackingId()
  {
    return $this->adTrackingId;
  }
  /**
   * @param string
   */
  public function setPath1($path1)
  {
    $this->path1 = $path1;
  }
  /**
   * @return string
   */
  public function getPath1()
  {
    return $this->path1;
  }
  /**
   * @param string
   */
  public function setPath2($path2)
  {
    $this->path2 = $path2;
  }
  /**
   * @return string
   */
  public function getPath2()
  {
    return $this->path2;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleAdsSearchads360V0CommonSearchAds360ResponsiveSearchAdInfo::class, 'Google_Service_SA360_GoogleAdsSearchads360V0CommonSearchAds360ResponsiveSearchAdInfo');
