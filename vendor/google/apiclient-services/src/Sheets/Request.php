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

namespace Google\Service\Sheets;

class Request extends \Google\Model
{
  protected $addBandingType = AddBandingRequest::class;
  protected $addBandingDataType = '';
  public $addBanding;
  protected $addChartType = AddChartRequest::class;
  protected $addChartDataType = '';
  public $addChart;
  protected $addConditionalFormatRuleType = AddConditionalFormatRuleRequest::class;
  protected $addConditionalFormatRuleDataType = '';
  public $addConditionalFormatRule;
  protected $addDataSourceType = AddDataSourceRequest::class;
  protected $addDataSourceDataType = '';
  public $addDataSource;
  protected $addDimensionGroupType = AddDimensionGroupRequest::class;
  protected $addDimensionGroupDataType = '';
  public $addDimensionGroup;
  protected $addFilterViewType = AddFilterViewRequest::class;
  protected $addFilterViewDataType = '';
  public $addFilterView;
  protected $addNamedRangeType = AddNamedRangeRequest::class;
  protected $addNamedRangeDataType = '';
  public $addNamedRange;
  protected $addProtectedRangeType = AddProtectedRangeRequest::class;
  protected $addProtectedRangeDataType = '';
  public $addProtectedRange;
  protected $addSheetType = AddSheetRequest::class;
  protected $addSheetDataType = '';
  public $addSheet;
  protected $addSlicerType = AddSlicerRequest::class;
  protected $addSlicerDataType = '';
  public $addSlicer;
  protected $appendCellsType = AppendCellsRequest::class;
  protected $appendCellsDataType = '';
  public $appendCells;
  protected $appendDimensionType = AppendDimensionRequest::class;
  protected $appendDimensionDataType = '';
  public $appendDimension;
  protected $autoFillType = AutoFillRequest::class;
  protected $autoFillDataType = '';
  public $autoFill;
  protected $autoResizeDimensionsType = AutoResizeDimensionsRequest::class;
  protected $autoResizeDimensionsDataType = '';
  public $autoResizeDimensions;
  protected $clearBasicFilterType = ClearBasicFilterRequest::class;
  protected $clearBasicFilterDataType = '';
  public $clearBasicFilter;
  protected $copyPasteType = CopyPasteRequest::class;
  protected $copyPasteDataType = '';
  public $copyPaste;
  protected $createDeveloperMetadataType = CreateDeveloperMetadataRequest::class;
  protected $createDeveloperMetadataDataType = '';
  public $createDeveloperMetadata;
  protected $cutPasteType = CutPasteRequest::class;
  protected $cutPasteDataType = '';
  public $cutPaste;
  protected $deleteBandingType = DeleteBandingRequest::class;
  protected $deleteBandingDataType = '';
  public $deleteBanding;
  protected $deleteConditionalFormatRuleType = DeleteConditionalFormatRuleRequest::class;
  protected $deleteConditionalFormatRuleDataType = '';
  public $deleteConditionalFormatRule;
  protected $deleteDataSourceType = DeleteDataSourceRequest::class;
  protected $deleteDataSourceDataType = '';
  public $deleteDataSource;
  protected $deleteDeveloperMetadataType = DeleteDeveloperMetadataRequest::class;
  protected $deleteDeveloperMetadataDataType = '';
  public $deleteDeveloperMetadata;
  protected $deleteDimensionType = DeleteDimensionRequest::class;
  protected $deleteDimensionDataType = '';
  public $deleteDimension;
  protected $deleteDimensionGroupType = DeleteDimensionGroupRequest::class;
  protected $deleteDimensionGroupDataType = '';
  public $deleteDimensionGroup;
  protected $deleteDuplicatesType = DeleteDuplicatesRequest::class;
  protected $deleteDuplicatesDataType = '';
  public $deleteDuplicates;
  protected $deleteEmbeddedObjectType = DeleteEmbeddedObjectRequest::class;
  protected $deleteEmbeddedObjectDataType = '';
  public $deleteEmbeddedObject;
  protected $deleteFilterViewType = DeleteFilterViewRequest::class;
  protected $deleteFilterViewDataType = '';
  public $deleteFilterView;
  protected $deleteNamedRangeType = DeleteNamedRangeRequest::class;
  protected $deleteNamedRangeDataType = '';
  public $deleteNamedRange;
  protected $deleteProtectedRangeType = DeleteProtectedRangeRequest::class;
  protected $deleteProtectedRangeDataType = '';
  public $deleteProtectedRange;
  protected $deleteRangeType = DeleteRangeRequest::class;
  protected $deleteRangeDataType = '';
  public $deleteRange;
  protected $deleteSheetType = DeleteSheetRequest::class;
  protected $deleteSheetDataType = '';
  public $deleteSheet;
  protected $duplicateFilterViewType = DuplicateFilterViewRequest::class;
  protected $duplicateFilterViewDataType = '';
  public $duplicateFilterView;
  protected $duplicateSheetType = DuplicateSheetRequest::class;
  protected $duplicateSheetDataType = '';
  public $duplicateSheet;
  protected $findReplaceType = FindReplaceRequest::class;
  protected $findReplaceDataType = '';
  public $findReplace;
  protected $insertDimensionType = InsertDimensionRequest::class;
  protected $insertDimensionDataType = '';
  public $insertDimension;
  protected $insertRangeType = InsertRangeRequest::class;
  protected $insertRangeDataType = '';
  public $insertRange;
  protected $mergeCellsType = MergeCellsRequest::class;
  protected $mergeCellsDataType = '';
  public $mergeCells;
  protected $moveDimensionType = MoveDimensionRequest::class;
  protected $moveDimensionDataType = '';
  public $moveDimension;
  protected $pasteDataType = PasteDataRequest::class;
  protected $pasteDataDataType = '';
  public $pasteData;
  protected $randomizeRangeType = RandomizeRangeRequest::class;
  protected $randomizeRangeDataType = '';
  public $randomizeRange;
  protected $refreshDataSourceType = RefreshDataSourceRequest::class;
  protected $refreshDataSourceDataType = '';
  public $refreshDataSource;
  protected $repeatCellType = RepeatCellRequest::class;
  protected $repeatCellDataType = '';
  public $repeatCell;
  protected $setBasicFilterType = SetBasicFilterRequest::class;
  protected $setBasicFilterDataType = '';
  public $setBasicFilter;
  protected $setDataValidationType = SetDataValidationRequest::class;
  protected $setDataValidationDataType = '';
  public $setDataValidation;
  protected $sortRangeType = SortRangeRequest::class;
  protected $sortRangeDataType = '';
  public $sortRange;
  protected $textToColumnsType = TextToColumnsRequest::class;
  protected $textToColumnsDataType = '';
  public $textToColumns;
  protected $trimWhitespaceType = TrimWhitespaceRequest::class;
  protected $trimWhitespaceDataType = '';
  public $trimWhitespace;
  protected $unmergeCellsType = UnmergeCellsRequest::class;
  protected $unmergeCellsDataType = '';
  public $unmergeCells;
  protected $updateBandingType = UpdateBandingRequest::class;
  protected $updateBandingDataType = '';
  public $updateBanding;
  protected $updateBordersType = UpdateBordersRequest::class;
  protected $updateBordersDataType = '';
  public $updateBorders;
  protected $updateCellsType = UpdateCellsRequest::class;
  protected $updateCellsDataType = '';
  public $updateCells;
  protected $updateChartSpecType = UpdateChartSpecRequest::class;
  protected $updateChartSpecDataType = '';
  public $updateChartSpec;
  protected $updateConditionalFormatRuleType = UpdateConditionalFormatRuleRequest::class;
  protected $updateConditionalFormatRuleDataType = '';
  public $updateConditionalFormatRule;
  protected $updateDataSourceType = UpdateDataSourceRequest::class;
  protected $updateDataSourceDataType = '';
  public $updateDataSource;
  protected $updateDeveloperMetadataType = UpdateDeveloperMetadataRequest::class;
  protected $updateDeveloperMetadataDataType = '';
  public $updateDeveloperMetadata;
  protected $updateDimensionGroupType = UpdateDimensionGroupRequest::class;
  protected $updateDimensionGroupDataType = '';
  public $updateDimensionGroup;
  protected $updateDimensionPropertiesType = UpdateDimensionPropertiesRequest::class;
  protected $updateDimensionPropertiesDataType = '';
  public $updateDimensionProperties;
  protected $updateEmbeddedObjectBorderType = UpdateEmbeddedObjectBorderRequest::class;
  protected $updateEmbeddedObjectBorderDataType = '';
  public $updateEmbeddedObjectBorder;
  protected $updateEmbeddedObjectPositionType = UpdateEmbeddedObjectPositionRequest::class;
  protected $updateEmbeddedObjectPositionDataType = '';
  public $updateEmbeddedObjectPosition;
  protected $updateFilterViewType = UpdateFilterViewRequest::class;
  protected $updateFilterViewDataType = '';
  public $updateFilterView;
  protected $updateNamedRangeType = UpdateNamedRangeRequest::class;
  protected $updateNamedRangeDataType = '';
  public $updateNamedRange;
  protected $updateProtectedRangeType = UpdateProtectedRangeRequest::class;
  protected $updateProtectedRangeDataType = '';
  public $updateProtectedRange;
  protected $updateSheetPropertiesType = UpdateSheetPropertiesRequest::class;
  protected $updateSheetPropertiesDataType = '';
  public $updateSheetProperties;
  protected $updateSlicerSpecType = UpdateSlicerSpecRequest::class;
  protected $updateSlicerSpecDataType = '';
  public $updateSlicerSpec;
  protected $updateSpreadsheetPropertiesType = UpdateSpreadsheetPropertiesRequest::class;
  protected $updateSpreadsheetPropertiesDataType = '';
  public $updateSpreadsheetProperties;

  /**
   * @param AddBandingRequest
   */
  public function setAddBanding(AddBandingRequest $addBanding)
  {
    $this->addBanding = $addBanding;
  }
  /**
   * @return AddBandingRequest
   */
  public function getAddBanding()
  {
    return $this->addBanding;
  }
  /**
   * @param AddChartRequest
   */
  public function setAddChart(AddChartRequest $addChart)
  {
    $this->addChart = $addChart;
  }
  /**
   * @return AddChartRequest
   */
  public function getAddChart()
  {
    return $this->addChart;
  }
  /**
   * @param AddConditionalFormatRuleRequest
   */
  public function setAddConditionalFormatRule(AddConditionalFormatRuleRequest $addConditionalFormatRule)
  {
    $this->addConditionalFormatRule = $addConditionalFormatRule;
  }
  /**
   * @return AddConditionalFormatRuleRequest
   */
  public function getAddConditionalFormatRule()
  {
    return $this->addConditionalFormatRule;
  }
  /**
   * @param AddDataSourceRequest
   */
  public function setAddDataSource(AddDataSourceRequest $addDataSource)
  {
    $this->addDataSource = $addDataSource;
  }
  /**
   * @return AddDataSourceRequest
   */
  public function getAddDataSource()
  {
    return $this->addDataSource;
  }
  /**
   * @param AddDimensionGroupRequest
   */
  public function setAddDimensionGroup(AddDimensionGroupRequest $addDimensionGroup)
  {
    $this->addDimensionGroup = $addDimensionGroup;
  }
  /**
   * @return AddDimensionGroupRequest
   */
  public function getAddDimensionGroup()
  {
    return $this->addDimensionGroup;
  }
  /**
   * @param AddFilterViewRequest
   */
  public function setAddFilterView(AddFilterViewRequest $addFilterView)
  {
    $this->addFilterView = $addFilterView;
  }
  /**
   * @return AddFilterViewRequest
   */
  public function getAddFilterView()
  {
    return $this->addFilterView;
  }
  /**
   * @param AddNamedRangeRequest
   */
  public function setAddNamedRange(AddNamedRangeRequest $addNamedRange)
  {
    $this->addNamedRange = $addNamedRange;
  }
  /**
   * @return AddNamedRangeRequest
   */
  public function getAddNamedRange()
  {
    return $this->addNamedRange;
  }
  /**
   * @param AddProtectedRangeRequest
   */
  public function setAddProtectedRange(AddProtectedRangeRequest $addProtectedRange)
  {
    $this->addProtectedRange = $addProtectedRange;
  }
  /**
   * @return AddProtectedRangeRequest
   */
  public function getAddProtectedRange()
  {
    return $this->addProtectedRange;
  }
  /**
   * @param AddSheetRequest
   */
  public function setAddSheet(AddSheetRequest $addSheet)
  {
    $this->addSheet = $addSheet;
  }
  /**
   * @return AddSheetRequest
   */
  public function getAddSheet()
  {
    return $this->addSheet;
  }
  /**
   * @param AddSlicerRequest
   */
  public function setAddSlicer(AddSlicerRequest $addSlicer)
  {
    $this->addSlicer = $addSlicer;
  }
  /**
   * @return AddSlicerRequest
   */
  public function getAddSlicer()
  {
    return $this->addSlicer;
  }
  /**
   * @param AppendCellsRequest
   */
  public function setAppendCells(AppendCellsRequest $appendCells)
  {
    $this->appendCells = $appendCells;
  }
  /**
   * @return AppendCellsRequest
   */
  public function getAppendCells()
  {
    return $this->appendCells;
  }
  /**
   * @param AppendDimensionRequest
   */
  public function setAppendDimension(AppendDimensionRequest $appendDimension)
  {
    $this->appendDimension = $appendDimension;
  }
  /**
   * @return AppendDimensionRequest
   */
  public function getAppendDimension()
  {
    return $this->appendDimension;
  }
  /**
   * @param AutoFillRequest
   */
  public function setAutoFill(AutoFillRequest $autoFill)
  {
    $this->autoFill = $autoFill;
  }
  /**
   * @return AutoFillRequest
   */
  public function getAutoFill()
  {
    return $this->autoFill;
  }
  /**
   * @param AutoResizeDimensionsRequest
   */
  public function setAutoResizeDimensions(AutoResizeDimensionsRequest $autoResizeDimensions)
  {
    $this->autoResizeDimensions = $autoResizeDimensions;
  }
  /**
   * @return AutoResizeDimensionsRequest
   */
  public function getAutoResizeDimensions()
  {
    return $this->autoResizeDimensions;
  }
  /**
   * @param ClearBasicFilterRequest
   */
  public function setClearBasicFilter(ClearBasicFilterRequest $clearBasicFilter)
  {
    $this->clearBasicFilter = $clearBasicFilter;
  }
  /**
   * @return ClearBasicFilterRequest
   */
  public function getClearBasicFilter()
  {
    return $this->clearBasicFilter;
  }
  /**
   * @param CopyPasteRequest
   */
  public function setCopyPaste(CopyPasteRequest $copyPaste)
  {
    $this->copyPaste = $copyPaste;
  }
  /**
   * @return CopyPasteRequest
   */
  public function getCopyPaste()
  {
    return $this->copyPaste;
  }
  /**
   * @param CreateDeveloperMetadataRequest
   */
  public function setCreateDeveloperMetadata(CreateDeveloperMetadataRequest $createDeveloperMetadata)
  {
    $this->createDeveloperMetadata = $createDeveloperMetadata;
  }
  /**
   * @return CreateDeveloperMetadataRequest
   */
  public function getCreateDeveloperMetadata()
  {
    return $this->createDeveloperMetadata;
  }
  /**
   * @param CutPasteRequest
   */
  public function setCutPaste(CutPasteRequest $cutPaste)
  {
    $this->cutPaste = $cutPaste;
  }
  /**
   * @return CutPasteRequest
   */
  public function getCutPaste()
  {
    return $this->cutPaste;
  }
  /**
   * @param DeleteBandingRequest
   */
  public function setDeleteBanding(DeleteBandingRequest $deleteBanding)
  {
    $this->deleteBanding = $deleteBanding;
  }
  /**
   * @return DeleteBandingRequest
   */
  public function getDeleteBanding()
  {
    return $this->deleteBanding;
  }
  /**
   * @param DeleteConditionalFormatRuleRequest
   */
  public function setDeleteConditionalFormatRule(DeleteConditionalFormatRuleRequest $deleteConditionalFormatRule)
  {
    $this->deleteConditionalFormatRule = $deleteConditionalFormatRule;
  }
  /**
   * @return DeleteConditionalFormatRuleRequest
   */
  public function getDeleteConditionalFormatRule()
  {
    return $this->deleteConditionalFormatRule;
  }
  /**
   * @param DeleteDataSourceRequest
   */
  public function setDeleteDataSource(DeleteDataSourceRequest $deleteDataSource)
  {
    $this->deleteDataSource = $deleteDataSource;
  }
  /**
   * @return DeleteDataSourceRequest
   */
  public function getDeleteDataSource()
  {
    return $this->deleteDataSource;
  }
  /**
   * @param DeleteDeveloperMetadataRequest
   */
  public function setDeleteDeveloperMetadata(DeleteDeveloperMetadataRequest $deleteDeveloperMetadata)
  {
    $this->deleteDeveloperMetadata = $deleteDeveloperMetadata;
  }
  /**
   * @return DeleteDeveloperMetadataRequest
   */
  public function getDeleteDeveloperMetadata()
  {
    return $this->deleteDeveloperMetadata;
  }
  /**
   * @param DeleteDimensionRequest
   */
  public function setDeleteDimension(DeleteDimensionRequest $deleteDimension)
  {
    $this->deleteDimension = $deleteDimension;
  }
  /**
   * @return DeleteDimensionRequest
   */
  public function getDeleteDimension()
  {
    return $this->deleteDimension;
  }
  /**
   * @param DeleteDimensionGroupRequest
   */
  public function setDeleteDimensionGroup(DeleteDimensionGroupRequest $deleteDimensionGroup)
  {
    $this->deleteDimensionGroup = $deleteDimensionGroup;
  }
  /**
   * @return DeleteDimensionGroupRequest
   */
  public function getDeleteDimensionGroup()
  {
    return $this->deleteDimensionGroup;
  }
  /**
   * @param DeleteDuplicatesRequest
   */
  public function setDeleteDuplicates(DeleteDuplicatesRequest $deleteDuplicates)
  {
    $this->deleteDuplicates = $deleteDuplicates;
  }
  /**
   * @return DeleteDuplicatesRequest
   */
  public function getDeleteDuplicates()
  {
    return $this->deleteDuplicates;
  }
  /**
   * @param DeleteEmbeddedObjectRequest
   */
  public function setDeleteEmbeddedObject(DeleteEmbeddedObjectRequest $deleteEmbeddedObject)
  {
    $this->deleteEmbeddedObject = $deleteEmbeddedObject;
  }
  /**
   * @return DeleteEmbeddedObjectRequest
   */
  public function getDeleteEmbeddedObject()
  {
    return $this->deleteEmbeddedObject;
  }
  /**
   * @param DeleteFilterViewRequest
   */
  public function setDeleteFilterView(DeleteFilterViewRequest $deleteFilterView)
  {
    $this->deleteFilterView = $deleteFilterView;
  }
  /**
   * @return DeleteFilterViewRequest
   */
  public function getDeleteFilterView()
  {
    return $this->deleteFilterView;
  }
  /**
   * @param DeleteNamedRangeRequest
   */
  public function setDeleteNamedRange(DeleteNamedRangeRequest $deleteNamedRange)
  {
    $this->deleteNamedRange = $deleteNamedRange;
  }
  /**
   * @return DeleteNamedRangeRequest
   */
  public function getDeleteNamedRange()
  {
    return $this->deleteNamedRange;
  }
  /**
   * @param DeleteProtectedRangeRequest
   */
  public function setDeleteProtectedRange(DeleteProtectedRangeRequest $deleteProtectedRange)
  {
    $this->deleteProtectedRange = $deleteProtectedRange;
  }
  /**
   * @return DeleteProtectedRangeRequest
   */
  public function getDeleteProtectedRange()
  {
    return $this->deleteProtectedRange;
  }
  /**
   * @param DeleteRangeRequest
   */
  public function setDeleteRange(DeleteRangeRequest $deleteRange)
  {
    $this->deleteRange = $deleteRange;
  }
  /**
   * @return DeleteRangeRequest
   */
  public function getDeleteRange()
  {
    return $this->deleteRange;
  }
  /**
   * @param DeleteSheetRequest
   */
  public function setDeleteSheet(DeleteSheetRequest $deleteSheet)
  {
    $this->deleteSheet = $deleteSheet;
  }
  /**
   * @return DeleteSheetRequest
   */
  public function getDeleteSheet()
  {
    return $this->deleteSheet;
  }
  /**
   * @param DuplicateFilterViewRequest
   */
  public function setDuplicateFilterView(DuplicateFilterViewRequest $duplicateFilterView)
  {
    $this->duplicateFilterView = $duplicateFilterView;
  }
  /**
   * @return DuplicateFilterViewRequest
   */
  public function getDuplicateFilterView()
  {
    return $this->duplicateFilterView;
  }
  /**
   * @param DuplicateSheetRequest
   */
  public function setDuplicateSheet(DuplicateSheetRequest $duplicateSheet)
  {
    $this->duplicateSheet = $duplicateSheet;
  }
  /**
   * @return DuplicateSheetRequest
   */
  public function getDuplicateSheet()
  {
    return $this->duplicateSheet;
  }
  /**
   * @param FindReplaceRequest
   */
  public function setFindReplace(FindReplaceRequest $findReplace)
  {
    $this->findReplace = $findReplace;
  }
  /**
   * @return FindReplaceRequest
   */
  public function getFindReplace()
  {
    return $this->findReplace;
  }
  /**
   * @param InsertDimensionRequest
   */
  public function setInsertDimension(InsertDimensionRequest $insertDimension)
  {
    $this->insertDimension = $insertDimension;
  }
  /**
   * @return InsertDimensionRequest
   */
  public function getInsertDimension()
  {
    return $this->insertDimension;
  }
  /**
   * @param InsertRangeRequest
   */
  public function setInsertRange(InsertRangeRequest $insertRange)
  {
    $this->insertRange = $insertRange;
  }
  /**
   * @return InsertRangeRequest
   */
  public function getInsertRange()
  {
    return $this->insertRange;
  }
  /**
   * @param MergeCellsRequest
   */
  public function setMergeCells(MergeCellsRequest $mergeCells)
  {
    $this->mergeCells = $mergeCells;
  }
  /**
   * @return MergeCellsRequest
   */
  public function getMergeCells()
  {
    return $this->mergeCells;
  }
  /**
   * @param MoveDimensionRequest
   */
  public function setMoveDimension(MoveDimensionRequest $moveDimension)
  {
    $this->moveDimension = $moveDimension;
  }
  /**
   * @return MoveDimensionRequest
   */
  public function getMoveDimension()
  {
    return $this->moveDimension;
  }
  /**
   * @param PasteDataRequest
   */
  public function setPasteData(PasteDataRequest $pasteData)
  {
    $this->pasteData = $pasteData;
  }
  /**
   * @return PasteDataRequest
   */
  public function getPasteData()
  {
    return $this->pasteData;
  }
  /**
   * @param RandomizeRangeRequest
   */
  public function setRandomizeRange(RandomizeRangeRequest $randomizeRange)
  {
    $this->randomizeRange = $randomizeRange;
  }
  /**
   * @return RandomizeRangeRequest
   */
  public function getRandomizeRange()
  {
    return $this->randomizeRange;
  }
  /**
   * @param RefreshDataSourceRequest
   */
  public function setRefreshDataSource(RefreshDataSourceRequest $refreshDataSource)
  {
    $this->refreshDataSource = $refreshDataSource;
  }
  /**
   * @return RefreshDataSourceRequest
   */
  public function getRefreshDataSource()
  {
    return $this->refreshDataSource;
  }
  /**
   * @param RepeatCellRequest
   */
  public function setRepeatCell(RepeatCellRequest $repeatCell)
  {
    $this->repeatCell = $repeatCell;
  }
  /**
   * @return RepeatCellRequest
   */
  public function getRepeatCell()
  {
    return $this->repeatCell;
  }
  /**
   * @param SetBasicFilterRequest
   */
  public function setSetBasicFilter(SetBasicFilterRequest $setBasicFilter)
  {
    $this->setBasicFilter = $setBasicFilter;
  }
  /**
   * @return SetBasicFilterRequest
   */
  public function getSetBasicFilter()
  {
    return $this->setBasicFilter;
  }
  /**
   * @param SetDataValidationRequest
   */
  public function setSetDataValidation(SetDataValidationRequest $setDataValidation)
  {
    $this->setDataValidation = $setDataValidation;
  }
  /**
   * @return SetDataValidationRequest
   */
  public function getSetDataValidation()
  {
    return $this->setDataValidation;
  }
  /**
   * @param SortRangeRequest
   */
  public function setSortRange(SortRangeRequest $sortRange)
  {
    $this->sortRange = $sortRange;
  }
  /**
   * @return SortRangeRequest
   */
  public function getSortRange()
  {
    return $this->sortRange;
  }
  /**
   * @param TextToColumnsRequest
   */
  public function setTextToColumns(TextToColumnsRequest $textToColumns)
  {
    $this->textToColumns = $textToColumns;
  }
  /**
   * @return TextToColumnsRequest
   */
  public function getTextToColumns()
  {
    return $this->textToColumns;
  }
  /**
   * @param TrimWhitespaceRequest
   */
  public function setTrimWhitespace(TrimWhitespaceRequest $trimWhitespace)
  {
    $this->trimWhitespace = $trimWhitespace;
  }
  /**
   * @return TrimWhitespaceRequest
   */
  public function getTrimWhitespace()
  {
    return $this->trimWhitespace;
  }
  /**
   * @param UnmergeCellsRequest
   */
  public function setUnmergeCells(UnmergeCellsRequest $unmergeCells)
  {
    $this->unmergeCells = $unmergeCells;
  }
  /**
   * @return UnmergeCellsRequest
   */
  public function getUnmergeCells()
  {
    return $this->unmergeCells;
  }
  /**
   * @param UpdateBandingRequest
   */
  public function setUpdateBanding(UpdateBandingRequest $updateBanding)
  {
    $this->updateBanding = $updateBanding;
  }
  /**
   * @return UpdateBandingRequest
   */
  public function getUpdateBanding()
  {
    return $this->updateBanding;
  }
  /**
   * @param UpdateBordersRequest
   */
  public function setUpdateBorders(UpdateBordersRequest $updateBorders)
  {
    $this->updateBorders = $updateBorders;
  }
  /**
   * @return UpdateBordersRequest
   */
  public function getUpdateBorders()
  {
    return $this->updateBorders;
  }
  /**
   * @param UpdateCellsRequest
   */
  public function setUpdateCells(UpdateCellsRequest $updateCells)
  {
    $this->updateCells = $updateCells;
  }
  /**
   * @return UpdateCellsRequest
   */
  public function getUpdateCells()
  {
    return $this->updateCells;
  }
  /**
   * @param UpdateChartSpecRequest
   */
  public function setUpdateChartSpec(UpdateChartSpecRequest $updateChartSpec)
  {
    $this->updateChartSpec = $updateChartSpec;
  }
  /**
   * @return UpdateChartSpecRequest
   */
  public function getUpdateChartSpec()
  {
    return $this->updateChartSpec;
  }
  /**
   * @param UpdateConditionalFormatRuleRequest
   */
  public function setUpdateConditionalFormatRule(UpdateConditionalFormatRuleRequest $updateConditionalFormatRule)
  {
    $this->updateConditionalFormatRule = $updateConditionalFormatRule;
  }
  /**
   * @return UpdateConditionalFormatRuleRequest
   */
  public function getUpdateConditionalFormatRule()
  {
    return $this->updateConditionalFormatRule;
  }
  /**
   * @param UpdateDataSourceRequest
   */
  public function setUpdateDataSource(UpdateDataSourceRequest $updateDataSource)
  {
    $this->updateDataSource = $updateDataSource;
  }
  /**
   * @return UpdateDataSourceRequest
   */
  public function getUpdateDataSource()
  {
    return $this->updateDataSource;
  }
  /**
   * @param UpdateDeveloperMetadataRequest
   */
  public function setUpdateDeveloperMetadata(UpdateDeveloperMetadataRequest $updateDeveloperMetadata)
  {
    $this->updateDeveloperMetadata = $updateDeveloperMetadata;
  }
  /**
   * @return UpdateDeveloperMetadataRequest
   */
  public function getUpdateDeveloperMetadata()
  {
    return $this->updateDeveloperMetadata;
  }
  /**
   * @param UpdateDimensionGroupRequest
   */
  public function setUpdateDimensionGroup(UpdateDimensionGroupRequest $updateDimensionGroup)
  {
    $this->updateDimensionGroup = $updateDimensionGroup;
  }
  /**
   * @return UpdateDimensionGroupRequest
   */
  public function getUpdateDimensionGroup()
  {
    return $this->updateDimensionGroup;
  }
  /**
   * @param UpdateDimensionPropertiesRequest
   */
  public function setUpdateDimensionProperties(UpdateDimensionPropertiesRequest $updateDimensionProperties)
  {
    $this->updateDimensionProperties = $updateDimensionProperties;
  }
  /**
   * @return UpdateDimensionPropertiesRequest
   */
  public function getUpdateDimensionProperties()
  {
    return $this->updateDimensionProperties;
  }
  /**
   * @param UpdateEmbeddedObjectBorderRequest
   */
  public function setUpdateEmbeddedObjectBorder(UpdateEmbeddedObjectBorderRequest $updateEmbeddedObjectBorder)
  {
    $this->updateEmbeddedObjectBorder = $updateEmbeddedObjectBorder;
  }
  /**
   * @return UpdateEmbeddedObjectBorderRequest
   */
  public function getUpdateEmbeddedObjectBorder()
  {
    return $this->updateEmbeddedObjectBorder;
  }
  /**
   * @param UpdateEmbeddedObjectPositionRequest
   */
  public function setUpdateEmbeddedObjectPosition(UpdateEmbeddedObjectPositionRequest $updateEmbeddedObjectPosition)
  {
    $this->updateEmbeddedObjectPosition = $updateEmbeddedObjectPosition;
  }
  /**
   * @return UpdateEmbeddedObjectPositionRequest
   */
  public function getUpdateEmbeddedObjectPosition()
  {
    return $this->updateEmbeddedObjectPosition;
  }
  /**
   * @param UpdateFilterViewRequest
   */
  public function setUpdateFilterView(UpdateFilterViewRequest $updateFilterView)
  {
    $this->updateFilterView = $updateFilterView;
  }
  /**
   * @return UpdateFilterViewRequest
   */
  public function getUpdateFilterView()
  {
    return $this->updateFilterView;
  }
  /**
   * @param UpdateNamedRangeRequest
   */
  public function setUpdateNamedRange(UpdateNamedRangeRequest $updateNamedRange)
  {
    $this->updateNamedRange = $updateNamedRange;
  }
  /**
   * @return UpdateNamedRangeRequest
   */
  public function getUpdateNamedRange()
  {
    return $this->updateNamedRange;
  }
  /**
   * @param UpdateProtectedRangeRequest
   */
  public function setUpdateProtectedRange(UpdateProtectedRangeRequest $updateProtectedRange)
  {
    $this->updateProtectedRange = $updateProtectedRange;
  }
  /**
   * @return UpdateProtectedRangeRequest
   */
  public function getUpdateProtectedRange()
  {
    return $this->updateProtectedRange;
  }
  /**
   * @param UpdateSheetPropertiesRequest
   */
  public function setUpdateSheetProperties(UpdateSheetPropertiesRequest $updateSheetProperties)
  {
    $this->updateSheetProperties = $updateSheetProperties;
  }
  /**
   * @return UpdateSheetPropertiesRequest
   */
  public function getUpdateSheetProperties()
  {
    return $this->updateSheetProperties;
  }
  /**
   * @param UpdateSlicerSpecRequest
   */
  public function setUpdateSlicerSpec(UpdateSlicerSpecRequest $updateSlicerSpec)
  {
    $this->updateSlicerSpec = $updateSlicerSpec;
  }
  /**
   * @return UpdateSlicerSpecRequest
   */
  public function getUpdateSlicerSpec()
  {
    return $this->updateSlicerSpec;
  }
  /**
   * @param UpdateSpreadsheetPropertiesRequest
   */
  public function setUpdateSpreadsheetProperties(UpdateSpreadsheetPropertiesRequest $updateSpreadsheetProperties)
  {
    $this->updateSpreadsheetProperties = $updateSpreadsheetProperties;
  }
  /**
   * @return UpdateSpreadsheetPropertiesRequest
   */
  public function getUpdateSpreadsheetProperties()
  {
    return $this->updateSpreadsheetProperties;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Request::class, 'Google_Service_Sheets_Request');
