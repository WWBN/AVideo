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

class Google_Service_Sheets_GradientRule extends Google_Model
{
  protected $maxpointType = 'Google_Service_Sheets_InterpolationPoint';
  protected $maxpointDataType = '';
  protected $midpointType = 'Google_Service_Sheets_InterpolationPoint';
  protected $midpointDataType = '';
  protected $minpointType = 'Google_Service_Sheets_InterpolationPoint';
  protected $minpointDataType = '';

  public function setMaxpoint(Google_Service_Sheets_InterpolationPoint $maxpoint)
  {
    $this->maxpoint = $maxpoint;
  }
  public function getMaxpoint()
  {
    return $this->maxpoint;
  }
  public function setMidpoint(Google_Service_Sheets_InterpolationPoint $midpoint)
  {
    $this->midpoint = $midpoint;
  }
  public function getMidpoint()
  {
    return $this->midpoint;
  }
  public function setMinpoint(Google_Service_Sheets_InterpolationPoint $minpoint)
  {
    $this->minpoint = $minpoint;
  }
  public function getMinpoint()
  {
    return $this->minpoint;
  }
}
