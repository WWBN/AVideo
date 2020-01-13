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

class Google_Service_Slides_ReplaceAllShapesWithSheetsChartRequest extends Google_Model
{
  public $chartId;
  protected $containsTextType = 'Google_Service_Slides_SubstringMatchCriteria';
  protected $containsTextDataType = '';
  public $linkingMode;
  public $spreadsheetId;

  public function setChartId($chartId)
  {
    $this->chartId = $chartId;
  }
  public function getChartId()
  {
    return $this->chartId;
  }
  public function setContainsText(Google_Service_Slides_SubstringMatchCriteria $containsText)
  {
    $this->containsText = $containsText;
  }
  public function getContainsText()
  {
    return $this->containsText;
  }
  public function setLinkingMode($linkingMode)
  {
    $this->linkingMode = $linkingMode;
  }
  public function getLinkingMode()
  {
    return $this->linkingMode;
  }
  public function setSpreadsheetId($spreadsheetId)
  {
    $this->spreadsheetId = $spreadsheetId;
  }
  public function getSpreadsheetId()
  {
    return $this->spreadsheetId;
  }
}
