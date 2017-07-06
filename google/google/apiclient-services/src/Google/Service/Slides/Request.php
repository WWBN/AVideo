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

class Google_Service_Slides_Request extends Google_Model
{
  protected $createImageType = 'Google_Service_Slides_CreateImageRequest';
  protected $createImageDataType = '';
  protected $createLineType = 'Google_Service_Slides_CreateLineRequest';
  protected $createLineDataType = '';
  protected $createParagraphBulletsType = 'Google_Service_Slides_CreateParagraphBulletsRequest';
  protected $createParagraphBulletsDataType = '';
  protected $createShapeType = 'Google_Service_Slides_CreateShapeRequest';
  protected $createShapeDataType = '';
  protected $createSheetsChartType = 'Google_Service_Slides_CreateSheetsChartRequest';
  protected $createSheetsChartDataType = '';
  protected $createSlideType = 'Google_Service_Slides_CreateSlideRequest';
  protected $createSlideDataType = '';
  protected $createTableType = 'Google_Service_Slides_CreateTableRequest';
  protected $createTableDataType = '';
  protected $createVideoType = 'Google_Service_Slides_CreateVideoRequest';
  protected $createVideoDataType = '';
  protected $deleteObjectType = 'Google_Service_Slides_DeleteObjectRequest';
  protected $deleteObjectDataType = '';
  protected $deleteParagraphBulletsType = 'Google_Service_Slides_DeleteParagraphBulletsRequest';
  protected $deleteParagraphBulletsDataType = '';
  protected $deleteTableColumnType = 'Google_Service_Slides_DeleteTableColumnRequest';
  protected $deleteTableColumnDataType = '';
  protected $deleteTableRowType = 'Google_Service_Slides_DeleteTableRowRequest';
  protected $deleteTableRowDataType = '';
  protected $deleteTextType = 'Google_Service_Slides_DeleteTextRequest';
  protected $deleteTextDataType = '';
  protected $duplicateObjectType = 'Google_Service_Slides_DuplicateObjectRequest';
  protected $duplicateObjectDataType = '';
  protected $insertTableColumnsType = 'Google_Service_Slides_InsertTableColumnsRequest';
  protected $insertTableColumnsDataType = '';
  protected $insertTableRowsType = 'Google_Service_Slides_InsertTableRowsRequest';
  protected $insertTableRowsDataType = '';
  protected $insertTextType = 'Google_Service_Slides_InsertTextRequest';
  protected $insertTextDataType = '';
  protected $refreshSheetsChartType = 'Google_Service_Slides_RefreshSheetsChartRequest';
  protected $refreshSheetsChartDataType = '';
  protected $replaceAllShapesWithImageType = 'Google_Service_Slides_ReplaceAllShapesWithImageRequest';
  protected $replaceAllShapesWithImageDataType = '';
  protected $replaceAllShapesWithSheetsChartType = 'Google_Service_Slides_ReplaceAllShapesWithSheetsChartRequest';
  protected $replaceAllShapesWithSheetsChartDataType = '';
  protected $replaceAllTextType = 'Google_Service_Slides_ReplaceAllTextRequest';
  protected $replaceAllTextDataType = '';
  protected $updateImagePropertiesType = 'Google_Service_Slides_UpdateImagePropertiesRequest';
  protected $updateImagePropertiesDataType = '';
  protected $updateLinePropertiesType = 'Google_Service_Slides_UpdateLinePropertiesRequest';
  protected $updateLinePropertiesDataType = '';
  protected $updatePageElementTransformType = 'Google_Service_Slides_UpdatePageElementTransformRequest';
  protected $updatePageElementTransformDataType = '';
  protected $updatePagePropertiesType = 'Google_Service_Slides_UpdatePagePropertiesRequest';
  protected $updatePagePropertiesDataType = '';
  protected $updateParagraphStyleType = 'Google_Service_Slides_UpdateParagraphStyleRequest';
  protected $updateParagraphStyleDataType = '';
  protected $updateShapePropertiesType = 'Google_Service_Slides_UpdateShapePropertiesRequest';
  protected $updateShapePropertiesDataType = '';
  protected $updateSlidesPositionType = 'Google_Service_Slides_UpdateSlidesPositionRequest';
  protected $updateSlidesPositionDataType = '';
  protected $updateTableCellPropertiesType = 'Google_Service_Slides_UpdateTableCellPropertiesRequest';
  protected $updateTableCellPropertiesDataType = '';
  protected $updateTextStyleType = 'Google_Service_Slides_UpdateTextStyleRequest';
  protected $updateTextStyleDataType = '';
  protected $updateVideoPropertiesType = 'Google_Service_Slides_UpdateVideoPropertiesRequest';
  protected $updateVideoPropertiesDataType = '';

  public function setCreateImage(Google_Service_Slides_CreateImageRequest $createImage)
  {
    $this->createImage = $createImage;
  }
  public function getCreateImage()
  {
    return $this->createImage;
  }
  public function setCreateLine(Google_Service_Slides_CreateLineRequest $createLine)
  {
    $this->createLine = $createLine;
  }
  public function getCreateLine()
  {
    return $this->createLine;
  }
  public function setCreateParagraphBullets(Google_Service_Slides_CreateParagraphBulletsRequest $createParagraphBullets)
  {
    $this->createParagraphBullets = $createParagraphBullets;
  }
  public function getCreateParagraphBullets()
  {
    return $this->createParagraphBullets;
  }
  public function setCreateShape(Google_Service_Slides_CreateShapeRequest $createShape)
  {
    $this->createShape = $createShape;
  }
  public function getCreateShape()
  {
    return $this->createShape;
  }
  public function setCreateSheetsChart(Google_Service_Slides_CreateSheetsChartRequest $createSheetsChart)
  {
    $this->createSheetsChart = $createSheetsChart;
  }
  public function getCreateSheetsChart()
  {
    return $this->createSheetsChart;
  }
  public function setCreateSlide(Google_Service_Slides_CreateSlideRequest $createSlide)
  {
    $this->createSlide = $createSlide;
  }
  public function getCreateSlide()
  {
    return $this->createSlide;
  }
  public function setCreateTable(Google_Service_Slides_CreateTableRequest $createTable)
  {
    $this->createTable = $createTable;
  }
  public function getCreateTable()
  {
    return $this->createTable;
  }
  public function setCreateVideo(Google_Service_Slides_CreateVideoRequest $createVideo)
  {
    $this->createVideo = $createVideo;
  }
  public function getCreateVideo()
  {
    return $this->createVideo;
  }
  public function setDeleteObject(Google_Service_Slides_DeleteObjectRequest $deleteObject)
  {
    $this->deleteObject = $deleteObject;
  }
  public function getDeleteObject()
  {
    return $this->deleteObject;
  }
  public function setDeleteParagraphBullets(Google_Service_Slides_DeleteParagraphBulletsRequest $deleteParagraphBullets)
  {
    $this->deleteParagraphBullets = $deleteParagraphBullets;
  }
  public function getDeleteParagraphBullets()
  {
    return $this->deleteParagraphBullets;
  }
  public function setDeleteTableColumn(Google_Service_Slides_DeleteTableColumnRequest $deleteTableColumn)
  {
    $this->deleteTableColumn = $deleteTableColumn;
  }
  public function getDeleteTableColumn()
  {
    return $this->deleteTableColumn;
  }
  public function setDeleteTableRow(Google_Service_Slides_DeleteTableRowRequest $deleteTableRow)
  {
    $this->deleteTableRow = $deleteTableRow;
  }
  public function getDeleteTableRow()
  {
    return $this->deleteTableRow;
  }
  public function setDeleteText(Google_Service_Slides_DeleteTextRequest $deleteText)
  {
    $this->deleteText = $deleteText;
  }
  public function getDeleteText()
  {
    return $this->deleteText;
  }
  public function setDuplicateObject(Google_Service_Slides_DuplicateObjectRequest $duplicateObject)
  {
    $this->duplicateObject = $duplicateObject;
  }
  public function getDuplicateObject()
  {
    return $this->duplicateObject;
  }
  public function setInsertTableColumns(Google_Service_Slides_InsertTableColumnsRequest $insertTableColumns)
  {
    $this->insertTableColumns = $insertTableColumns;
  }
  public function getInsertTableColumns()
  {
    return $this->insertTableColumns;
  }
  public function setInsertTableRows(Google_Service_Slides_InsertTableRowsRequest $insertTableRows)
  {
    $this->insertTableRows = $insertTableRows;
  }
  public function getInsertTableRows()
  {
    return $this->insertTableRows;
  }
  public function setInsertText(Google_Service_Slides_InsertTextRequest $insertText)
  {
    $this->insertText = $insertText;
  }
  public function getInsertText()
  {
    return $this->insertText;
  }
  public function setRefreshSheetsChart(Google_Service_Slides_RefreshSheetsChartRequest $refreshSheetsChart)
  {
    $this->refreshSheetsChart = $refreshSheetsChart;
  }
  public function getRefreshSheetsChart()
  {
    return $this->refreshSheetsChart;
  }
  public function setReplaceAllShapesWithImage(Google_Service_Slides_ReplaceAllShapesWithImageRequest $replaceAllShapesWithImage)
  {
    $this->replaceAllShapesWithImage = $replaceAllShapesWithImage;
  }
  public function getReplaceAllShapesWithImage()
  {
    return $this->replaceAllShapesWithImage;
  }
  public function setReplaceAllShapesWithSheetsChart(Google_Service_Slides_ReplaceAllShapesWithSheetsChartRequest $replaceAllShapesWithSheetsChart)
  {
    $this->replaceAllShapesWithSheetsChart = $replaceAllShapesWithSheetsChart;
  }
  public function getReplaceAllShapesWithSheetsChart()
  {
    return $this->replaceAllShapesWithSheetsChart;
  }
  public function setReplaceAllText(Google_Service_Slides_ReplaceAllTextRequest $replaceAllText)
  {
    $this->replaceAllText = $replaceAllText;
  }
  public function getReplaceAllText()
  {
    return $this->replaceAllText;
  }
  public function setUpdateImageProperties(Google_Service_Slides_UpdateImagePropertiesRequest $updateImageProperties)
  {
    $this->updateImageProperties = $updateImageProperties;
  }
  public function getUpdateImageProperties()
  {
    return $this->updateImageProperties;
  }
  public function setUpdateLineProperties(Google_Service_Slides_UpdateLinePropertiesRequest $updateLineProperties)
  {
    $this->updateLineProperties = $updateLineProperties;
  }
  public function getUpdateLineProperties()
  {
    return $this->updateLineProperties;
  }
  public function setUpdatePageElementTransform(Google_Service_Slides_UpdatePageElementTransformRequest $updatePageElementTransform)
  {
    $this->updatePageElementTransform = $updatePageElementTransform;
  }
  public function getUpdatePageElementTransform()
  {
    return $this->updatePageElementTransform;
  }
  public function setUpdatePageProperties(Google_Service_Slides_UpdatePagePropertiesRequest $updatePageProperties)
  {
    $this->updatePageProperties = $updatePageProperties;
  }
  public function getUpdatePageProperties()
  {
    return $this->updatePageProperties;
  }
  public function setUpdateParagraphStyle(Google_Service_Slides_UpdateParagraphStyleRequest $updateParagraphStyle)
  {
    $this->updateParagraphStyle = $updateParagraphStyle;
  }
  public function getUpdateParagraphStyle()
  {
    return $this->updateParagraphStyle;
  }
  public function setUpdateShapeProperties(Google_Service_Slides_UpdateShapePropertiesRequest $updateShapeProperties)
  {
    $this->updateShapeProperties = $updateShapeProperties;
  }
  public function getUpdateShapeProperties()
  {
    return $this->updateShapeProperties;
  }
  public function setUpdateSlidesPosition(Google_Service_Slides_UpdateSlidesPositionRequest $updateSlidesPosition)
  {
    $this->updateSlidesPosition = $updateSlidesPosition;
  }
  public function getUpdateSlidesPosition()
  {
    return $this->updateSlidesPosition;
  }
  public function setUpdateTableCellProperties(Google_Service_Slides_UpdateTableCellPropertiesRequest $updateTableCellProperties)
  {
    $this->updateTableCellProperties = $updateTableCellProperties;
  }
  public function getUpdateTableCellProperties()
  {
    return $this->updateTableCellProperties;
  }
  public function setUpdateTextStyle(Google_Service_Slides_UpdateTextStyleRequest $updateTextStyle)
  {
    $this->updateTextStyle = $updateTextStyle;
  }
  public function getUpdateTextStyle()
  {
    return $this->updateTextStyle;
  }
  public function setUpdateVideoProperties(Google_Service_Slides_UpdateVideoPropertiesRequest $updateVideoProperties)
  {
    $this->updateVideoProperties = $updateVideoProperties;
  }
  public function getUpdateVideoProperties()
  {
    return $this->updateVideoProperties;
  }
}
