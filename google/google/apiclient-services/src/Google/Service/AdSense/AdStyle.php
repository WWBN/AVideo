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

class Google_Service_AdSense_AdStyle extends Google_Model
{
  protected $colorsType = 'Google_Service_AdSense_AdStyleColors';
  protected $colorsDataType = '';
  public $corners;
  protected $fontType = 'Google_Service_AdSense_AdStyleFont';
  protected $fontDataType = '';
  public $kind;

  public function setColors(Google_Service_AdSense_AdStyleColors $colors)
  {
    $this->colors = $colors;
  }
  public function getColors()
  {
    return $this->colors;
  }
  public function setCorners($corners)
  {
    $this->corners = $corners;
  }
  public function getCorners()
  {
    return $this->corners;
  }
  public function setFont(Google_Service_AdSense_AdStyleFont $font)
  {
    $this->font = $font;
  }
  public function getFont()
  {
    return $this->font;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
}
