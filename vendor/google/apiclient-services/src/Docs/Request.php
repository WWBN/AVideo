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

namespace Google\Service\Docs;

class Request extends \Google\Model
{
  protected $createFooterType = CreateFooterRequest::class;
  protected $createFooterDataType = '';
  public $createFooter;
  protected $createFootnoteType = CreateFootnoteRequest::class;
  protected $createFootnoteDataType = '';
  public $createFootnote;
  protected $createHeaderType = CreateHeaderRequest::class;
  protected $createHeaderDataType = '';
  public $createHeader;
  protected $createNamedRangeType = CreateNamedRangeRequest::class;
  protected $createNamedRangeDataType = '';
  public $createNamedRange;
  protected $createParagraphBulletsType = CreateParagraphBulletsRequest::class;
  protected $createParagraphBulletsDataType = '';
  public $createParagraphBullets;
  protected $deleteContentRangeType = DeleteContentRangeRequest::class;
  protected $deleteContentRangeDataType = '';
  public $deleteContentRange;
  protected $deleteFooterType = DeleteFooterRequest::class;
  protected $deleteFooterDataType = '';
  public $deleteFooter;
  protected $deleteHeaderType = DeleteHeaderRequest::class;
  protected $deleteHeaderDataType = '';
  public $deleteHeader;
  protected $deleteNamedRangeType = DeleteNamedRangeRequest::class;
  protected $deleteNamedRangeDataType = '';
  public $deleteNamedRange;
  protected $deleteParagraphBulletsType = DeleteParagraphBulletsRequest::class;
  protected $deleteParagraphBulletsDataType = '';
  public $deleteParagraphBullets;
  protected $deletePositionedObjectType = DeletePositionedObjectRequest::class;
  protected $deletePositionedObjectDataType = '';
  public $deletePositionedObject;
  protected $deleteTableColumnType = DeleteTableColumnRequest::class;
  protected $deleteTableColumnDataType = '';
  public $deleteTableColumn;
  protected $deleteTableRowType = DeleteTableRowRequest::class;
  protected $deleteTableRowDataType = '';
  public $deleteTableRow;
  protected $insertInlineImageType = InsertInlineImageRequest::class;
  protected $insertInlineImageDataType = '';
  public $insertInlineImage;
  protected $insertPageBreakType = InsertPageBreakRequest::class;
  protected $insertPageBreakDataType = '';
  public $insertPageBreak;
  protected $insertSectionBreakType = InsertSectionBreakRequest::class;
  protected $insertSectionBreakDataType = '';
  public $insertSectionBreak;
  protected $insertTableType = InsertTableRequest::class;
  protected $insertTableDataType = '';
  public $insertTable;
  protected $insertTableColumnType = InsertTableColumnRequest::class;
  protected $insertTableColumnDataType = '';
  public $insertTableColumn;
  protected $insertTableRowType = InsertTableRowRequest::class;
  protected $insertTableRowDataType = '';
  public $insertTableRow;
  protected $insertTextType = InsertTextRequest::class;
  protected $insertTextDataType = '';
  public $insertText;
  protected $mergeTableCellsType = MergeTableCellsRequest::class;
  protected $mergeTableCellsDataType = '';
  public $mergeTableCells;
  protected $pinTableHeaderRowsType = PinTableHeaderRowsRequest::class;
  protected $pinTableHeaderRowsDataType = '';
  public $pinTableHeaderRows;
  protected $replaceAllTextType = ReplaceAllTextRequest::class;
  protected $replaceAllTextDataType = '';
  public $replaceAllText;
  protected $replaceImageType = ReplaceImageRequest::class;
  protected $replaceImageDataType = '';
  public $replaceImage;
  protected $replaceNamedRangeContentType = ReplaceNamedRangeContentRequest::class;
  protected $replaceNamedRangeContentDataType = '';
  public $replaceNamedRangeContent;
  protected $unmergeTableCellsType = UnmergeTableCellsRequest::class;
  protected $unmergeTableCellsDataType = '';
  public $unmergeTableCells;
  protected $updateDocumentStyleType = UpdateDocumentStyleRequest::class;
  protected $updateDocumentStyleDataType = '';
  public $updateDocumentStyle;
  protected $updateParagraphStyleType = UpdateParagraphStyleRequest::class;
  protected $updateParagraphStyleDataType = '';
  public $updateParagraphStyle;
  protected $updateSectionStyleType = UpdateSectionStyleRequest::class;
  protected $updateSectionStyleDataType = '';
  public $updateSectionStyle;
  protected $updateTableCellStyleType = UpdateTableCellStyleRequest::class;
  protected $updateTableCellStyleDataType = '';
  public $updateTableCellStyle;
  protected $updateTableColumnPropertiesType = UpdateTableColumnPropertiesRequest::class;
  protected $updateTableColumnPropertiesDataType = '';
  public $updateTableColumnProperties;
  protected $updateTableRowStyleType = UpdateTableRowStyleRequest::class;
  protected $updateTableRowStyleDataType = '';
  public $updateTableRowStyle;
  protected $updateTextStyleType = UpdateTextStyleRequest::class;
  protected $updateTextStyleDataType = '';
  public $updateTextStyle;

  /**
   * @param CreateFooterRequest
   */
  public function setCreateFooter(CreateFooterRequest $createFooter)
  {
    $this->createFooter = $createFooter;
  }
  /**
   * @return CreateFooterRequest
   */
  public function getCreateFooter()
  {
    return $this->createFooter;
  }
  /**
   * @param CreateFootnoteRequest
   */
  public function setCreateFootnote(CreateFootnoteRequest $createFootnote)
  {
    $this->createFootnote = $createFootnote;
  }
  /**
   * @return CreateFootnoteRequest
   */
  public function getCreateFootnote()
  {
    return $this->createFootnote;
  }
  /**
   * @param CreateHeaderRequest
   */
  public function setCreateHeader(CreateHeaderRequest $createHeader)
  {
    $this->createHeader = $createHeader;
  }
  /**
   * @return CreateHeaderRequest
   */
  public function getCreateHeader()
  {
    return $this->createHeader;
  }
  /**
   * @param CreateNamedRangeRequest
   */
  public function setCreateNamedRange(CreateNamedRangeRequest $createNamedRange)
  {
    $this->createNamedRange = $createNamedRange;
  }
  /**
   * @return CreateNamedRangeRequest
   */
  public function getCreateNamedRange()
  {
    return $this->createNamedRange;
  }
  /**
   * @param CreateParagraphBulletsRequest
   */
  public function setCreateParagraphBullets(CreateParagraphBulletsRequest $createParagraphBullets)
  {
    $this->createParagraphBullets = $createParagraphBullets;
  }
  /**
   * @return CreateParagraphBulletsRequest
   */
  public function getCreateParagraphBullets()
  {
    return $this->createParagraphBullets;
  }
  /**
   * @param DeleteContentRangeRequest
   */
  public function setDeleteContentRange(DeleteContentRangeRequest $deleteContentRange)
  {
    $this->deleteContentRange = $deleteContentRange;
  }
  /**
   * @return DeleteContentRangeRequest
   */
  public function getDeleteContentRange()
  {
    return $this->deleteContentRange;
  }
  /**
   * @param DeleteFooterRequest
   */
  public function setDeleteFooter(DeleteFooterRequest $deleteFooter)
  {
    $this->deleteFooter = $deleteFooter;
  }
  /**
   * @return DeleteFooterRequest
   */
  public function getDeleteFooter()
  {
    return $this->deleteFooter;
  }
  /**
   * @param DeleteHeaderRequest
   */
  public function setDeleteHeader(DeleteHeaderRequest $deleteHeader)
  {
    $this->deleteHeader = $deleteHeader;
  }
  /**
   * @return DeleteHeaderRequest
   */
  public function getDeleteHeader()
  {
    return $this->deleteHeader;
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
   * @param DeleteParagraphBulletsRequest
   */
  public function setDeleteParagraphBullets(DeleteParagraphBulletsRequest $deleteParagraphBullets)
  {
    $this->deleteParagraphBullets = $deleteParagraphBullets;
  }
  /**
   * @return DeleteParagraphBulletsRequest
   */
  public function getDeleteParagraphBullets()
  {
    return $this->deleteParagraphBullets;
  }
  /**
   * @param DeletePositionedObjectRequest
   */
  public function setDeletePositionedObject(DeletePositionedObjectRequest $deletePositionedObject)
  {
    $this->deletePositionedObject = $deletePositionedObject;
  }
  /**
   * @return DeletePositionedObjectRequest
   */
  public function getDeletePositionedObject()
  {
    return $this->deletePositionedObject;
  }
  /**
   * @param DeleteTableColumnRequest
   */
  public function setDeleteTableColumn(DeleteTableColumnRequest $deleteTableColumn)
  {
    $this->deleteTableColumn = $deleteTableColumn;
  }
  /**
   * @return DeleteTableColumnRequest
   */
  public function getDeleteTableColumn()
  {
    return $this->deleteTableColumn;
  }
  /**
   * @param DeleteTableRowRequest
   */
  public function setDeleteTableRow(DeleteTableRowRequest $deleteTableRow)
  {
    $this->deleteTableRow = $deleteTableRow;
  }
  /**
   * @return DeleteTableRowRequest
   */
  public function getDeleteTableRow()
  {
    return $this->deleteTableRow;
  }
  /**
   * @param InsertInlineImageRequest
   */
  public function setInsertInlineImage(InsertInlineImageRequest $insertInlineImage)
  {
    $this->insertInlineImage = $insertInlineImage;
  }
  /**
   * @return InsertInlineImageRequest
   */
  public function getInsertInlineImage()
  {
    return $this->insertInlineImage;
  }
  /**
   * @param InsertPageBreakRequest
   */
  public function setInsertPageBreak(InsertPageBreakRequest $insertPageBreak)
  {
    $this->insertPageBreak = $insertPageBreak;
  }
  /**
   * @return InsertPageBreakRequest
   */
  public function getInsertPageBreak()
  {
    return $this->insertPageBreak;
  }
  /**
   * @param InsertSectionBreakRequest
   */
  public function setInsertSectionBreak(InsertSectionBreakRequest $insertSectionBreak)
  {
    $this->insertSectionBreak = $insertSectionBreak;
  }
  /**
   * @return InsertSectionBreakRequest
   */
  public function getInsertSectionBreak()
  {
    return $this->insertSectionBreak;
  }
  /**
   * @param InsertTableRequest
   */
  public function setInsertTable(InsertTableRequest $insertTable)
  {
    $this->insertTable = $insertTable;
  }
  /**
   * @return InsertTableRequest
   */
  public function getInsertTable()
  {
    return $this->insertTable;
  }
  /**
   * @param InsertTableColumnRequest
   */
  public function setInsertTableColumn(InsertTableColumnRequest $insertTableColumn)
  {
    $this->insertTableColumn = $insertTableColumn;
  }
  /**
   * @return InsertTableColumnRequest
   */
  public function getInsertTableColumn()
  {
    return $this->insertTableColumn;
  }
  /**
   * @param InsertTableRowRequest
   */
  public function setInsertTableRow(InsertTableRowRequest $insertTableRow)
  {
    $this->insertTableRow = $insertTableRow;
  }
  /**
   * @return InsertTableRowRequest
   */
  public function getInsertTableRow()
  {
    return $this->insertTableRow;
  }
  /**
   * @param InsertTextRequest
   */
  public function setInsertText(InsertTextRequest $insertText)
  {
    $this->insertText = $insertText;
  }
  /**
   * @return InsertTextRequest
   */
  public function getInsertText()
  {
    return $this->insertText;
  }
  /**
   * @param MergeTableCellsRequest
   */
  public function setMergeTableCells(MergeTableCellsRequest $mergeTableCells)
  {
    $this->mergeTableCells = $mergeTableCells;
  }
  /**
   * @return MergeTableCellsRequest
   */
  public function getMergeTableCells()
  {
    return $this->mergeTableCells;
  }
  /**
   * @param PinTableHeaderRowsRequest
   */
  public function setPinTableHeaderRows(PinTableHeaderRowsRequest $pinTableHeaderRows)
  {
    $this->pinTableHeaderRows = $pinTableHeaderRows;
  }
  /**
   * @return PinTableHeaderRowsRequest
   */
  public function getPinTableHeaderRows()
  {
    return $this->pinTableHeaderRows;
  }
  /**
   * @param ReplaceAllTextRequest
   */
  public function setReplaceAllText(ReplaceAllTextRequest $replaceAllText)
  {
    $this->replaceAllText = $replaceAllText;
  }
  /**
   * @return ReplaceAllTextRequest
   */
  public function getReplaceAllText()
  {
    return $this->replaceAllText;
  }
  /**
   * @param ReplaceImageRequest
   */
  public function setReplaceImage(ReplaceImageRequest $replaceImage)
  {
    $this->replaceImage = $replaceImage;
  }
  /**
   * @return ReplaceImageRequest
   */
  public function getReplaceImage()
  {
    return $this->replaceImage;
  }
  /**
   * @param ReplaceNamedRangeContentRequest
   */
  public function setReplaceNamedRangeContent(ReplaceNamedRangeContentRequest $replaceNamedRangeContent)
  {
    $this->replaceNamedRangeContent = $replaceNamedRangeContent;
  }
  /**
   * @return ReplaceNamedRangeContentRequest
   */
  public function getReplaceNamedRangeContent()
  {
    return $this->replaceNamedRangeContent;
  }
  /**
   * @param UnmergeTableCellsRequest
   */
  public function setUnmergeTableCells(UnmergeTableCellsRequest $unmergeTableCells)
  {
    $this->unmergeTableCells = $unmergeTableCells;
  }
  /**
   * @return UnmergeTableCellsRequest
   */
  public function getUnmergeTableCells()
  {
    return $this->unmergeTableCells;
  }
  /**
   * @param UpdateDocumentStyleRequest
   */
  public function setUpdateDocumentStyle(UpdateDocumentStyleRequest $updateDocumentStyle)
  {
    $this->updateDocumentStyle = $updateDocumentStyle;
  }
  /**
   * @return UpdateDocumentStyleRequest
   */
  public function getUpdateDocumentStyle()
  {
    return $this->updateDocumentStyle;
  }
  /**
   * @param UpdateParagraphStyleRequest
   */
  public function setUpdateParagraphStyle(UpdateParagraphStyleRequest $updateParagraphStyle)
  {
    $this->updateParagraphStyle = $updateParagraphStyle;
  }
  /**
   * @return UpdateParagraphStyleRequest
   */
  public function getUpdateParagraphStyle()
  {
    return $this->updateParagraphStyle;
  }
  /**
   * @param UpdateSectionStyleRequest
   */
  public function setUpdateSectionStyle(UpdateSectionStyleRequest $updateSectionStyle)
  {
    $this->updateSectionStyle = $updateSectionStyle;
  }
  /**
   * @return UpdateSectionStyleRequest
   */
  public function getUpdateSectionStyle()
  {
    return $this->updateSectionStyle;
  }
  /**
   * @param UpdateTableCellStyleRequest
   */
  public function setUpdateTableCellStyle(UpdateTableCellStyleRequest $updateTableCellStyle)
  {
    $this->updateTableCellStyle = $updateTableCellStyle;
  }
  /**
   * @return UpdateTableCellStyleRequest
   */
  public function getUpdateTableCellStyle()
  {
    return $this->updateTableCellStyle;
  }
  /**
   * @param UpdateTableColumnPropertiesRequest
   */
  public function setUpdateTableColumnProperties(UpdateTableColumnPropertiesRequest $updateTableColumnProperties)
  {
    $this->updateTableColumnProperties = $updateTableColumnProperties;
  }
  /**
   * @return UpdateTableColumnPropertiesRequest
   */
  public function getUpdateTableColumnProperties()
  {
    return $this->updateTableColumnProperties;
  }
  /**
   * @param UpdateTableRowStyleRequest
   */
  public function setUpdateTableRowStyle(UpdateTableRowStyleRequest $updateTableRowStyle)
  {
    $this->updateTableRowStyle = $updateTableRowStyle;
  }
  /**
   * @return UpdateTableRowStyleRequest
   */
  public function getUpdateTableRowStyle()
  {
    return $this->updateTableRowStyle;
  }
  /**
   * @param UpdateTextStyleRequest
   */
  public function setUpdateTextStyle(UpdateTextStyleRequest $updateTextStyle)
  {
    $this->updateTextStyle = $updateTextStyle;
  }
  /**
   * @return UpdateTextStyleRequest
   */
  public function getUpdateTextStyle()
  {
    return $this->updateTextStyle;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Request::class, 'Google_Service_Docs_Request');
