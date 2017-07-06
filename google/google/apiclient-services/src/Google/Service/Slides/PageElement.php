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

class Google_Service_Slides_PageElement extends Google_Model
{
  public $description;
  protected $elementGroupType = 'Google_Service_Slides_Group';
  protected $elementGroupDataType = '';
  protected $imageType = 'Google_Service_Slides_Image';
  protected $imageDataType = '';
  protected $lineType = 'Google_Service_Slides_Line';
  protected $lineDataType = '';
  public $objectId;
  protected $shapeType = 'Google_Service_Slides_Shape';
  protected $shapeDataType = '';
  protected $sheetsChartType = 'Google_Service_Slides_SheetsChart';
  protected $sheetsChartDataType = '';
  protected $sizeType = 'Google_Service_Slides_Size';
  protected $sizeDataType = '';
  protected $tableType = 'Google_Service_Slides_Table';
  protected $tableDataType = '';
  public $title;
  protected $transformType = 'Google_Service_Slides_AffineTransform';
  protected $transformDataType = '';
  protected $videoType = 'Google_Service_Slides_Video';
  protected $videoDataType = '';
  protected $wordArtType = 'Google_Service_Slides_WordArt';
  protected $wordArtDataType = '';

  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setElementGroup(Google_Service_Slides_Group $elementGroup)
  {
    $this->elementGroup = $elementGroup;
  }
  public function getElementGroup()
  {
    return $this->elementGroup;
  }
  public function setImage(Google_Service_Slides_Image $image)
  {
    $this->image = $image;
  }
  public function getImage()
  {
    return $this->image;
  }
  public function setLine(Google_Service_Slides_Line $line)
  {
    $this->line = $line;
  }
  public function getLine()
  {
    return $this->line;
  }
  public function setObjectId($objectId)
  {
    $this->objectId = $objectId;
  }
  public function getObjectId()
  {
    return $this->objectId;
  }
  public function setShape(Google_Service_Slides_Shape $shape)
  {
    $this->shape = $shape;
  }
  public function getShape()
  {
    return $this->shape;
  }
  public function setSheetsChart(Google_Service_Slides_SheetsChart $sheetsChart)
  {
    $this->sheetsChart = $sheetsChart;
  }
  public function getSheetsChart()
  {
    return $this->sheetsChart;
  }
  public function setSize(Google_Service_Slides_Size $size)
  {
    $this->size = $size;
  }
  public function getSize()
  {
    return $this->size;
  }
  public function setTable(Google_Service_Slides_Table $table)
  {
    $this->table = $table;
  }
  public function getTable()
  {
    return $this->table;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
  public function setTransform(Google_Service_Slides_AffineTransform $transform)
  {
    $this->transform = $transform;
  }
  public function getTransform()
  {
    return $this->transform;
  }
  public function setVideo(Google_Service_Slides_Video $video)
  {
    $this->video = $video;
  }
  public function getVideo()
  {
    return $this->video;
  }
  public function setWordArt(Google_Service_Slides_WordArt $wordArt)
  {
    $this->wordArt = $wordArt;
  }
  public function getWordArt()
  {
    return $this->wordArt;
  }
}
