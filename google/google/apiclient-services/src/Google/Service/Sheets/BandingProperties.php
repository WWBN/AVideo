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

class Google_Service_Sheets_BandingProperties extends Google_Model
{
  protected $firstBandColorType = 'Google_Service_Sheets_Color';
  protected $firstBandColorDataType = '';
  protected $footerColorType = 'Google_Service_Sheets_Color';
  protected $footerColorDataType = '';
  protected $headerColorType = 'Google_Service_Sheets_Color';
  protected $headerColorDataType = '';
  protected $secondBandColorType = 'Google_Service_Sheets_Color';
  protected $secondBandColorDataType = '';

  public function setFirstBandColor(Google_Service_Sheets_Color $firstBandColor)
  {
    $this->firstBandColor = $firstBandColor;
  }
  public function getFirstBandColor()
  {
    return $this->firstBandColor;
  }
  public function setFooterColor(Google_Service_Sheets_Color $footerColor)
  {
    $this->footerColor = $footerColor;
  }
  public function getFooterColor()
  {
    return $this->footerColor;
  }
  public function setHeaderColor(Google_Service_Sheets_Color $headerColor)
  {
    $this->headerColor = $headerColor;
  }
  public function getHeaderColor()
  {
    return $this->headerColor;
  }
  public function setSecondBandColor(Google_Service_Sheets_Color $secondBandColor)
  {
    $this->secondBandColor = $secondBandColor;
  }
  public function getSecondBandColor()
  {
    return $this->secondBandColor;
  }
}
