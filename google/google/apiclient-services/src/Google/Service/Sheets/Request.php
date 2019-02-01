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

class Google_Service_Sheets_Request extends Google_Model
{
  protected $addBandingType = 'Google_Service_Sheets_AddBandingRequest';
  protected $addBandingDataType = '';
  protected $addChartType = 'Google_Service_Sheets_AddChartRequest';
  protected $addChartDataType = '';
  protected $addConditionalFormatRuleType = 'Google_Service_Sheets_AddConditionalFormatRuleRequest';
  protected $addConditionalFormatRuleDataType = '';
  protected $addFilterViewType = 'Google_Service_Sheets_AddFilterViewRequest';
  protected $addFilterViewDataType = '';
  protected $addNamedRangeType = 'Google_Service_Sheets_AddNamedRangeRequest';
  protected $addNamedRangeDataType = '';
  protected $addProtectedRangeType = 'Google_Service_Sheets_AddProtectedRangeRequest';
  protected $addProtectedRangeDataType = '';
  protected $addSheetType = 'Google_Service_Sheets_AddSheetRequest';
  protected $addSheetDataType = '';
  protected $appendCellsType = 'Google_Service_Sheets_AppendCellsRequest';
  protected $appendCellsDataType = '';
  protected $appendDimensionType = 'Google_Service_Sheets_AppendDimensionRequest';
  protected $appendDimensionDataType = '';
  protected $autoFillType = 'Google_Service_Sheets_AutoFillRequest';
  protected $autoFillDataType = '';
  protected $autoResizeDimensionsType = 'Google_Service_Sheets_AutoResizeDimensionsRequest';
  protected $autoResizeDimensionsDataType = '';
  protected $clearBasicFilterType = 'Google_Service_Sheets_ClearBasicFilterRequest';
  protected $clearBasicFilterDataType = '';
  protected $copyPasteType = 'Google_Service_Sheets_CopyPasteRequest';
  protected $copyPasteDataType = '';
  protected $cutPasteType = 'Google_Service_Sheets_CutPasteRequest';
  protected $cutPasteDataType = '';
  protected $deleteBandingType = 'Google_Service_Sheets_DeleteBandingRequest';
  protected $deleteBandingDataType = '';
  protected $deleteConditionalFormatRuleType = 'Google_Service_Sheets_DeleteConditionalFormatRuleRequest';
  protected $deleteConditionalFormatRuleDataType = '';
  protected $deleteDimensionType = 'Google_Service_Sheets_DeleteDimensionRequest';
  protected $deleteDimensionDataType = '';
  protected $deleteEmbeddedObjectType = 'Google_Service_Sheets_DeleteEmbeddedObjectRequest';
  protected $deleteEmbeddedObjectDataType = '';
  protected $deleteFilterViewType = 'Google_Service_Sheets_DeleteFilterViewRequest';
  protected $deleteFilterViewDataType = '';
  protected $deleteNamedRangeType = 'Google_Service_Sheets_DeleteNamedRangeRequest';
  protected $deleteNamedRangeDataType = '';
  protected $deleteProtectedRangeType = 'Google_Service_Sheets_DeleteProtectedRangeRequest';
  protected $deleteProtectedRangeDataType = '';
  protected $deleteRangeType = 'Google_Service_Sheets_DeleteRangeRequest';
  protected $deleteRangeDataType = '';
  protected $deleteSheetType = 'Google_Service_Sheets_DeleteSheetRequest';
  protected $deleteSheetDataType = '';
  protected $duplicateFilterViewType = 'Google_Service_Sheets_DuplicateFilterViewRequest';
  protected $duplicateFilterViewDataType = '';
  protected $duplicateSheetType = 'Google_Service_Sheets_DuplicateSheetRequest';
  protected $duplicateSheetDataType = '';
  protected $findReplaceType = 'Google_Service_Sheets_FindReplaceRequest';
  protected $findReplaceDataType = '';
  protected $insertDimensionType = 'Google_Service_Sheets_InsertDimensionRequest';
  protected $insertDimensionDataType = '';
  protected $insertRangeType = 'Google_Service_Sheets_InsertRangeRequest';
  protected $insertRangeDataType = '';
  protected $mergeCellsType = 'Google_Service_Sheets_MergeCellsRequest';
  protected $mergeCellsDataType = '';
  protected $moveDimensionType = 'Google_Service_Sheets_MoveDimensionRequest';
  protected $moveDimensionDataType = '';
  protected $pasteDataType = 'Google_Service_Sheets_PasteDataRequest';
  protected $pasteDataDataType = '';
  protected $repeatCellType = 'Google_Service_Sheets_RepeatCellRequest';
  protected $repeatCellDataType = '';
  protected $setBasicFilterType = 'Google_Service_Sheets_SetBasicFilterRequest';
  protected $setBasicFilterDataType = '';
  protected $setDataValidationType = 'Google_Service_Sheets_SetDataValidationRequest';
  protected $setDataValidationDataType = '';
  protected $sortRangeType = 'Google_Service_Sheets_SortRangeRequest';
  protected $sortRangeDataType = '';
  protected $textToColumnsType = 'Google_Service_Sheets_TextToColumnsRequest';
  protected $textToColumnsDataType = '';
  protected $unmergeCellsType = 'Google_Service_Sheets_UnmergeCellsRequest';
  protected $unmergeCellsDataType = '';
  protected $updateBandingType = 'Google_Service_Sheets_UpdateBandingRequest';
  protected $updateBandingDataType = '';
  protected $updateBordersType = 'Google_Service_Sheets_UpdateBordersRequest';
  protected $updateBordersDataType = '';
  protected $updateCellsType = 'Google_Service_Sheets_UpdateCellsRequest';
  protected $updateCellsDataType = '';
  protected $updateChartSpecType = 'Google_Service_Sheets_UpdateChartSpecRequest';
  protected $updateChartSpecDataType = '';
  protected $updateConditionalFormatRuleType = 'Google_Service_Sheets_UpdateConditionalFormatRuleRequest';
  protected $updateConditionalFormatRuleDataType = '';
  protected $updateDimensionPropertiesType = 'Google_Service_Sheets_UpdateDimensionPropertiesRequest';
  protected $updateDimensionPropertiesDataType = '';
  protected $updateEmbeddedObjectPositionType = 'Google_Service_Sheets_UpdateEmbeddedObjectPositionRequest';
  protected $updateEmbeddedObjectPositionDataType = '';
  protected $updateFilterViewType = 'Google_Service_Sheets_UpdateFilterViewRequest';
  protected $updateFilterViewDataType = '';
  protected $updateNamedRangeType = 'Google_Service_Sheets_UpdateNamedRangeRequest';
  protected $updateNamedRangeDataType = '';
  protected $updateProtectedRangeType = 'Google_Service_Sheets_UpdateProtectedRangeRequest';
  protected $updateProtectedRangeDataType = '';
  protected $updateSheetPropertiesType = 'Google_Service_Sheets_UpdateSheetPropertiesRequest';
  protected $updateSheetPropertiesDataType = '';
  protected $updateSpreadsheetPropertiesType = 'Google_Service_Sheets_UpdateSpreadsheetPropertiesRequest';
  protected $updateSpreadsheetPropertiesDataType = '';

  public function setAddBanding(Google_Service_Sheets_AddBandingRequest $addBanding)
  {
    $this->addBanding = $addBanding;
  }
  public function getAddBanding()
  {
    return $this->addBanding;
  }
  public function setAddChart(Google_Service_Sheets_AddChartRequest $addChart)
  {
    $this->addChart = $addChart;
  }
  public function getAddChart()
  {
    return $this->addChart;
  }
  public function setAddConditionalFormatRule(Google_Service_Sheets_AddConditionalFormatRuleRequest $addConditionalFormatRule)
  {
    $this->addConditionalFormatRule = $addConditionalFormatRule;
  }
  public function getAddConditionalFormatRule()
  {
    return $this->addConditionalFormatRule;
  }
  public function setAddFilterView(Google_Service_Sheets_AddFilterViewRequest $addFilterView)
  {
    $this->addFilterView = $addFilterView;
  }
  public function getAddFilterView()
  {
    return $this->addFilterView;
  }
  public function setAddNamedRange(Google_Service_Sheets_AddNamedRangeRequest $addNamedRange)
  {
    $this->addNamedRange = $addNamedRange;
  }
  public function getAddNamedRange()
  {
    return $this->addNamedRange;
  }
  public function setAddProtectedRange(Google_Service_Sheets_AddProtectedRangeRequest $addProtectedRange)
  {
    $this->addProtectedRange = $addProtectedRange;
  }
  public function getAddProtectedRange()
  {
    return $this->addProtectedRange;
  }
  public function setAddSheet(Google_Service_Sheets_AddSheetRequest $addSheet)
  {
    $this->addSheet = $addSheet;
  }
  public function getAddSheet()
  {
    return $this->addSheet;
  }
  public function setAppendCells(Google_Service_Sheets_AppendCellsRequest $appendCells)
  {
    $this->appendCells = $appendCells;
  }
  public function getAppendCells()
  {
    return $this->appendCells;
  }
  public function setAppendDimension(Google_Service_Sheets_AppendDimensionRequest $appendDimension)
  {
    $this->appendDimension = $appendDimension;
  }
  public function getAppendDimension()
  {
    return $this->appendDimension;
  }
  public function setAutoFill(Google_Service_Sheets_AutoFillRequest $autoFill)
  {
    $this->autoFill = $autoFill;
  }
  public function getAutoFill()
  {
    return $this->autoFill;
  }
  public function setAutoResizeDimensions(Google_Service_Sheets_AutoResizeDimensionsRequest $autoResizeDimensions)
  {
    $this->autoResizeDimensions = $autoResizeDimensions;
  }
  public function getAutoResizeDimensions()
  {
    return $this->autoResizeDimensions;
  }
  public function setClearBasicFilter(Google_Service_Sheets_ClearBasicFilterRequest $clearBasicFilter)
  {
    $this->clearBasicFilter = $clearBasicFilter;
  }
  public function getClearBasicFilter()
  {
    return $this->clearBasicFilter;
  }
  public function setCopyPaste(Google_Service_Sheets_CopyPasteRequest $copyPaste)
  {
    $this->copyPaste = $copyPaste;
  }
  public function getCopyPaste()
  {
    return $this->copyPaste;
  }
  public function setCutPaste(Google_Service_Sheets_CutPasteRequest $cutPaste)
  {
    $this->cutPaste = $cutPaste;
  }
  public function getCutPaste()
  {
    return $this->cutPaste;
  }
  public function setDeleteBanding(Google_Service_Sheets_DeleteBandingRequest $deleteBanding)
  {
    $this->deleteBanding = $deleteBanding;
  }
  public function getDeleteBanding()
  {
    return $this->deleteBanding;
  }
  public function setDeleteConditionalFormatRule(Google_Service_Sheets_DeleteConditionalFormatRuleRequest $deleteConditionalFormatRule)
  {
    $this->deleteConditionalFormatRule = $deleteConditionalFormatRule;
  }
  public function getDeleteConditionalFormatRule()
  {
    return $this->deleteConditionalFormatRule;
  }
  public function setDeleteDimension(Google_Service_Sheets_DeleteDimensionRequest $deleteDimension)
  {
    $this->deleteDimension = $deleteDimension;
  }
  public function getDeleteDimension()
  {
    return $this->deleteDimension;
  }
  public function setDeleteEmbeddedObject(Google_Service_Sheets_DeleteEmbeddedObjectRequest $deleteEmbeddedObject)
  {
    $this->deleteEmbeddedObject = $deleteEmbeddedObject;
  }
  public function getDeleteEmbeddedObject()
  {
    return $this->deleteEmbeddedObject;
  }
  public function setDeleteFilterView(Google_Service_Sheets_DeleteFilterViewRequest $deleteFilterView)
  {
    $this->deleteFilterView = $deleteFilterView;
  }
  public function getDeleteFilterView()
  {
    return $this->deleteFilterView;
  }
  public function setDeleteNamedRange(Google_Service_Sheets_DeleteNamedRangeRequest $deleteNamedRange)
  {
    $this->deleteNamedRange = $deleteNamedRange;
  }
  public function getDeleteNamedRange()
  {
    return $this->deleteNamedRange;
  }
  public function setDeleteProtectedRange(Google_Service_Sheets_DeleteProtectedRangeRequest $deleteProtectedRange)
  {
    $this->deleteProtectedRange = $deleteProtectedRange;
  }
  public function getDeleteProtectedRange()
  {
    return $this->deleteProtectedRange;
  }
  public function setDeleteRange(Google_Service_Sheets_DeleteRangeRequest $deleteRange)
  {
    $this->deleteRange = $deleteRange;
  }
  public function getDeleteRange()
  {
    return $this->deleteRange;
  }
  public function setDeleteSheet(Google_Service_Sheets_DeleteSheetRequest $deleteSheet)
  {
    $this->deleteSheet = $deleteSheet;
  }
  public function getDeleteSheet()
  {
    return $this->deleteSheet;
  }
  public function setDuplicateFilterView(Google_Service_Sheets_DuplicateFilterViewRequest $duplicateFilterView)
  {
    $this->duplicateFilterView = $duplicateFilterView;
  }
  public function getDuplicateFilterView()
  {
    return $this->duplicateFilterView;
  }
  public function setDuplicateSheet(Google_Service_Sheets_DuplicateSheetRequest $duplicateSheet)
  {
    $this->duplicateSheet = $duplicateSheet;
  }
  public function getDuplicateSheet()
  {
    return $this->duplicateSheet;
  }
  public function setFindReplace(Google_Service_Sheets_FindReplaceRequest $findReplace)
  {
    $this->findReplace = $findReplace;
  }
  public function getFindReplace()
  {
    return $this->findReplace;
  }
  public function setInsertDimension(Google_Service_Sheets_InsertDimensionRequest $insertDimension)
  {
    $this->insertDimension = $insertDimension;
  }
  public function getInsertDimension()
  {
    return $this->insertDimension;
  }
  public function setInsertRange(Google_Service_Sheets_InsertRangeRequest $insertRange)
  {
    $this->insertRange = $insertRange;
  }
  public function getInsertRange()
  {
    return $this->insertRange;
  }
  public function setMergeCells(Google_Service_Sheets_MergeCellsRequest $mergeCells)
  {
    $this->mergeCells = $mergeCells;
  }
  public function getMergeCells()
  {
    return $this->mergeCells;
  }
  public function setMoveDimension(Google_Service_Sheets_MoveDimensionRequest $moveDimension)
  {
    $this->moveDimension = $moveDimension;
  }
  public function getMoveDimension()
  {
    return $this->moveDimension;
  }
  public function setPasteData(Google_Service_Sheets_PasteDataRequest $pasteData)
  {
    $this->pasteData = $pasteData;
  }
  public function getPasteData()
  {
    return $this->pasteData;
  }
  public function setRepeatCell(Google_Service_Sheets_RepeatCellRequest $repeatCell)
  {
    $this->repeatCell = $repeatCell;
  }
  public function getRepeatCell()
  {
    return $this->repeatCell;
  }
  public function setSetBasicFilter(Google_Service_Sheets_SetBasicFilterRequest $setBasicFilter)
  {
    $this->setBasicFilter = $setBasicFilter;
  }
  public function getSetBasicFilter()
  {
    return $this->setBasicFilter;
  }
  public function setSetDataValidation(Google_Service_Sheets_SetDataValidationRequest $setDataValidation)
  {
    $this->setDataValidation = $setDataValidation;
  }
  public function getSetDataValidation()
  {
    return $this->setDataValidation;
  }
  public function setSortRange(Google_Service_Sheets_SortRangeRequest $sortRange)
  {
    $this->sortRange = $sortRange;
  }
  public function getSortRange()
  {
    return $this->sortRange;
  }
  public function setTextToColumns(Google_Service_Sheets_TextToColumnsRequest $textToColumns)
  {
    $this->textToColumns = $textToColumns;
  }
  public function getTextToColumns()
  {
    return $this->textToColumns;
  }
  public function setUnmergeCells(Google_Service_Sheets_UnmergeCellsRequest $unmergeCells)
  {
    $this->unmergeCells = $unmergeCells;
  }
  public function getUnmergeCells()
  {
    return $this->unmergeCells;
  }
  public function setUpdateBanding(Google_Service_Sheets_UpdateBandingRequest $updateBanding)
  {
    $this->updateBanding = $updateBanding;
  }
  public function getUpdateBanding()
  {
    return $this->updateBanding;
  }
  public function setUpdateBorders(Google_Service_Sheets_UpdateBordersRequest $updateBorders)
  {
    $this->updateBorders = $updateBorders;
  }
  public function getUpdateBorders()
  {
    return $this->updateBorders;
  }
  public function setUpdateCells(Google_Service_Sheets_UpdateCellsRequest $updateCells)
  {
    $this->updateCells = $updateCells;
  }
  public function getUpdateCells()
  {
    return $this->updateCells;
  }
  public function setUpdateChartSpec(Google_Service_Sheets_UpdateChartSpecRequest $updateChartSpec)
  {
    $this->updateChartSpec = $updateChartSpec;
  }
  public function getUpdateChartSpec()
  {
    return $this->updateChartSpec;
  }
  public function setUpdateConditionalFormatRule(Google_Service_Sheets_UpdateConditionalFormatRuleRequest $updateConditionalFormatRule)
  {
    $this->updateConditionalFormatRule = $updateConditionalFormatRule;
  }
  public function getUpdateConditionalFormatRule()
  {
    return $this->updateConditionalFormatRule;
  }
  public function setUpdateDimensionProperties(Google_Service_Sheets_UpdateDimensionPropertiesRequest $updateDimensionProperties)
  {
    $this->updateDimensionProperties = $updateDimensionProperties;
  }
  public function getUpdateDimensionProperties()
  {
    return $this->updateDimensionProperties;
  }
  public function setUpdateEmbeddedObjectPosition(Google_Service_Sheets_UpdateEmbeddedObjectPositionRequest $updateEmbeddedObjectPosition)
  {
    $this->updateEmbeddedObjectPosition = $updateEmbeddedObjectPosition;
  }
  public function getUpdateEmbeddedObjectPosition()
  {
    return $this->updateEmbeddedObjectPosition;
  }
  public function setUpdateFilterView(Google_Service_Sheets_UpdateFilterViewRequest $updateFilterView)
  {
    $this->updateFilterView = $updateFilterView;
  }
  public function getUpdateFilterView()
  {
    return $this->updateFilterView;
  }
  public function setUpdateNamedRange(Google_Service_Sheets_UpdateNamedRangeRequest $updateNamedRange)
  {
    $this->updateNamedRange = $updateNamedRange;
  }
  public function getUpdateNamedRange()
  {
    return $this->updateNamedRange;
  }
  public function setUpdateProtectedRange(Google_Service_Sheets_UpdateProtectedRangeRequest $updateProtectedRange)
  {
    $this->updateProtectedRange = $updateProtectedRange;
  }
  public function getUpdateProtectedRange()
  {
    return $this->updateProtectedRange;
  }
  public function setUpdateSheetProperties(Google_Service_Sheets_UpdateSheetPropertiesRequest $updateSheetProperties)
  {
    $this->updateSheetProperties = $updateSheetProperties;
  }
  public function getUpdateSheetProperties()
  {
    return $this->updateSheetProperties;
  }
  public function setUpdateSpreadsheetProperties(Google_Service_Sheets_UpdateSpreadsheetPropertiesRequest $updateSpreadsheetProperties)
  {
    $this->updateSpreadsheetProperties = $updateSpreadsheetProperties;
  }
  public function getUpdateSpreadsheetProperties()
  {
    return $this->updateSpreadsheetProperties;
  }
}
