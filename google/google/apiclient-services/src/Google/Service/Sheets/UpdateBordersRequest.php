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

class Google_Service_Sheets_UpdateBordersRequest extends Google_Model
{
  protected $bottomType = 'Google_Service_Sheets_Border';
  protected $bottomDataType = '';
  protected $innerHorizontalType = 'Google_Service_Sheets_Border';
  protected $innerHorizontalDataType = '';
  protected $innerVerticalType = 'Google_Service_Sheets_Border';
  protected $innerVerticalDataType = '';
  protected $leftType = 'Google_Service_Sheets_Border';
  protected $leftDataType = '';
  protected $rangeType = 'Google_Service_Sheets_GridRange';
  protected $rangeDataType = '';
  protected $rightType = 'Google_Service_Sheets_Border';
  protected $rightDataType = '';
  protected $topType = 'Google_Service_Sheets_Border';
  protected $topDataType = '';

  public function setBottom(Google_Service_Sheets_Border $bottom)
  {
    $this->bottom = $bottom;
  }
  public function getBottom()
  {
    return $this->bottom;
  }
  public function setInnerHorizontal(Google_Service_Sheets_Border $innerHorizontal)
  {
    $this->innerHorizontal = $innerHorizontal;
  }
  public function getInnerHorizontal()
  {
    return $this->innerHorizontal;
  }
  public function setInnerVertical(Google_Service_Sheets_Border $innerVertical)
  {
    $this->innerVertical = $innerVertical;
  }
  public function getInnerVertical()
  {
    return $this->innerVertical;
  }
  public function setLeft(Google_Service_Sheets_Border $left)
  {
    $this->left = $left;
  }
  public function getLeft()
  {
    return $this->left;
  }
  public function setRange(Google_Service_Sheets_GridRange $range)
  {
    $this->range = $range;
  }
  public function getRange()
  {
    return $this->range;
  }
  public function setRight(Google_Service_Sheets_Border $right)
  {
    $this->right = $right;
  }
  public function getRight()
  {
    return $this->right;
  }
  public function setTop(Google_Service_Sheets_Border $top)
  {
    $this->top = $top;
  }
  public function getTop()
  {
    return $this->top;
  }
}
