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

class Google_Service_Slides_LineProperties extends Google_Model
{
  public $dashStyle;
  public $endArrow;
  protected $lineFillType = 'Google_Service_Slides_LineFill';
  protected $lineFillDataType = '';
  protected $linkType = 'Google_Service_Slides_Link';
  protected $linkDataType = '';
  public $startArrow;
  protected $weightType = 'Google_Service_Slides_Dimension';
  protected $weightDataType = '';

  public function setDashStyle($dashStyle)
  {
    $this->dashStyle = $dashStyle;
  }
  public function getDashStyle()
  {
    return $this->dashStyle;
  }
  public function setEndArrow($endArrow)
  {
    $this->endArrow = $endArrow;
  }
  public function getEndArrow()
  {
    return $this->endArrow;
  }
  public function setLineFill(Google_Service_Slides_LineFill $lineFill)
  {
    $this->lineFill = $lineFill;
  }
  public function getLineFill()
  {
    return $this->lineFill;
  }
  public function setLink(Google_Service_Slides_Link $link)
  {
    $this->link = $link;
  }
  public function getLink()
  {
    return $this->link;
  }
  public function setStartArrow($startArrow)
  {
    $this->startArrow = $startArrow;
  }
  public function getStartArrow()
  {
    return $this->startArrow;
  }
  public function setWeight(Google_Service_Slides_Dimension $weight)
  {
    $this->weight = $weight;
  }
  public function getWeight()
  {
    return $this->weight;
  }
}
