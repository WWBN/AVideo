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

class Google_Service_Vision_Block extends Google_Collection
{
  protected $collection_key = 'paragraphs';
  public $blockType;
  protected $boundingBoxType = 'Google_Service_Vision_BoundingPoly';
  protected $boundingBoxDataType = '';
  protected $paragraphsType = 'Google_Service_Vision_Paragraph';
  protected $paragraphsDataType = 'array';
  protected $propertyType = 'Google_Service_Vision_TextProperty';
  protected $propertyDataType = '';

  public function setBlockType($blockType)
  {
    $this->blockType = $blockType;
  }
  public function getBlockType()
  {
    return $this->blockType;
  }
  public function setBoundingBox(Google_Service_Vision_BoundingPoly $boundingBox)
  {
    $this->boundingBox = $boundingBox;
  }
  public function getBoundingBox()
  {
    return $this->boundingBox;
  }
  public function setParagraphs($paragraphs)
  {
    $this->paragraphs = $paragraphs;
  }
  public function getParagraphs()
  {
    return $this->paragraphs;
  }
  public function setProperty(Google_Service_Vision_TextProperty $property)
  {
    $this->property = $property;
  }
  public function getProperty()
  {
    return $this->property;
  }
}
