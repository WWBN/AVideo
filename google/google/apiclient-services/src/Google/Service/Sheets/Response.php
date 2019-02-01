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

class Google_Service_Sheets_Response extends Google_Model
{
  protected $addBandingType = 'Google_Service_Sheets_AddBandingResponse';
  protected $addBandingDataType = '';
  protected $addChartType = 'Google_Service_Sheets_AddChartResponse';
  protected $addChartDataType = '';
  protected $addFilterViewType = 'Google_Service_Sheets_AddFilterViewResponse';
  protected $addFilterViewDataType = '';
  protected $addNamedRangeType = 'Google_Service_Sheets_AddNamedRangeResponse';
  protected $addNamedRangeDataType = '';
  protected $addProtectedRangeType = 'Google_Service_Sheets_AddProtectedRangeResponse';
  protected $addProtectedRangeDataType = '';
  protected $addSheetType = 'Google_Service_Sheets_AddSheetResponse';
  protected $addSheetDataType = '';
  protected $deleteConditionalFormatRuleType = 'Google_Service_Sheets_DeleteConditionalFormatRuleResponse';
  protected $deleteConditionalFormatRuleDataType = '';
  protected $duplicateFilterViewType = 'Google_Service_Sheets_DuplicateFilterViewResponse';
  protected $duplicateFilterViewDataType = '';
  protected $duplicateSheetType = 'Google_Service_Sheets_DuplicateSheetResponse';
  protected $duplicateSheetDataType = '';
  protected $findReplaceType = 'Google_Service_Sheets_FindReplaceResponse';
  protected $findReplaceDataType = '';
  protected $updateConditionalFormatRuleType = 'Google_Service_Sheets_UpdateConditionalFormatRuleResponse';
  protected $updateConditionalFormatRuleDataType = '';
  protected $updateEmbeddedObjectPositionType = 'Google_Service_Sheets_UpdateEmbeddedObjectPositionResponse';
  protected $updateEmbeddedObjectPositionDataType = '';

  public function setAddBanding(Google_Service_Sheets_AddBandingResponse $addBanding)
  {
    $this->addBanding = $addBanding;
  }
  public function getAddBanding()
  {
    return $this->addBanding;
  }
  public function setAddChart(Google_Service_Sheets_AddChartResponse $addChart)
  {
    $this->addChart = $addChart;
  }
  public function getAddChart()
  {
    return $this->addChart;
  }
  public function setAddFilterView(Google_Service_Sheets_AddFilterViewResponse $addFilterView)
  {
    $this->addFilterView = $addFilterView;
  }
  public function getAddFilterView()
  {
    return $this->addFilterView;
  }
  public function setAddNamedRange(Google_Service_Sheets_AddNamedRangeResponse $addNamedRange)
  {
    $this->addNamedRange = $addNamedRange;
  }
  public function getAddNamedRange()
  {
    return $this->addNamedRange;
  }
  public function setAddProtectedRange(Google_Service_Sheets_AddProtectedRangeResponse $addProtectedRange)
  {
    $this->addProtectedRange = $addProtectedRange;
  }
  public function getAddProtectedRange()
  {
    return $this->addProtectedRange;
  }
  public function setAddSheet(Google_Service_Sheets_AddSheetResponse $addSheet)
  {
    $this->addSheet = $addSheet;
  }
  public function getAddSheet()
  {
    return $this->addSheet;
  }
  public function setDeleteConditionalFormatRule(Google_Service_Sheets_DeleteConditionalFormatRuleResponse $deleteConditionalFormatRule)
  {
    $this->deleteConditionalFormatRule = $deleteConditionalFormatRule;
  }
  public function getDeleteConditionalFormatRule()
  {
    return $this->deleteConditionalFormatRule;
  }
  public function setDuplicateFilterView(Google_Service_Sheets_DuplicateFilterViewResponse $duplicateFilterView)
  {
    $this->duplicateFilterView = $duplicateFilterView;
  }
  public function getDuplicateFilterView()
  {
    return $this->duplicateFilterView;
  }
  public function setDuplicateSheet(Google_Service_Sheets_DuplicateSheetResponse $duplicateSheet)
  {
    $this->duplicateSheet = $duplicateSheet;
  }
  public function getDuplicateSheet()
  {
    return $this->duplicateSheet;
  }
  public function setFindReplace(Google_Service_Sheets_FindReplaceResponse $findReplace)
  {
    $this->findReplace = $findReplace;
  }
  public function getFindReplace()
  {
    return $this->findReplace;
  }
  public function setUpdateConditionalFormatRule(Google_Service_Sheets_UpdateConditionalFormatRuleResponse $updateConditionalFormatRule)
  {
    $this->updateConditionalFormatRule = $updateConditionalFormatRule;
  }
  public function getUpdateConditionalFormatRule()
  {
    return $this->updateConditionalFormatRule;
  }
  public function setUpdateEmbeddedObjectPosition(Google_Service_Sheets_UpdateEmbeddedObjectPositionResponse $updateEmbeddedObjectPosition)
  {
    $this->updateEmbeddedObjectPosition = $updateEmbeddedObjectPosition;
  }
  public function getUpdateEmbeddedObjectPosition()
  {
    return $this->updateEmbeddedObjectPosition;
  }
}
