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

class Google_Service_Sheets_Spreadsheet extends Google_Collection
{
  protected $collection_key = 'sheets';
  protected $namedRangesType = 'Google_Service_Sheets_NamedRange';
  protected $namedRangesDataType = 'array';
  protected $propertiesType = 'Google_Service_Sheets_SpreadsheetProperties';
  protected $propertiesDataType = '';
  protected $sheetsType = 'Google_Service_Sheets_Sheet';
  protected $sheetsDataType = 'array';
  public $spreadsheetId;
  public $spreadsheetUrl;

  public function setNamedRanges($namedRanges)
  {
    $this->namedRanges = $namedRanges;
  }
  public function getNamedRanges()
  {
    return $this->namedRanges;
  }
  public function setProperties(Google_Service_Sheets_SpreadsheetProperties $properties)
  {
    $this->properties = $properties;
  }
  public function getProperties()
  {
    return $this->properties;
  }
  public function setSheets($sheets)
  {
    $this->sheets = $sheets;
  }
  public function getSheets()
  {
    return $this->sheets;
  }
  public function setSpreadsheetId($spreadsheetId)
  {
    $this->spreadsheetId = $spreadsheetId;
  }
  public function getSpreadsheetId()
  {
    return $this->spreadsheetId;
  }
  public function setSpreadsheetUrl($spreadsheetUrl)
  {
    $this->spreadsheetUrl = $spreadsheetUrl;
  }
  public function getSpreadsheetUrl()
  {
    return $this->spreadsheetUrl;
  }
}
