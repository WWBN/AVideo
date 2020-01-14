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

class Google_Service_Slides_Presentation extends Google_Collection
{
  protected $collection_key = 'slides';
  protected $layoutsType = 'Google_Service_Slides_Page';
  protected $layoutsDataType = 'array';
  public $locale;
  protected $mastersType = 'Google_Service_Slides_Page';
  protected $mastersDataType = 'array';
  protected $notesMasterType = 'Google_Service_Slides_Page';
  protected $notesMasterDataType = '';
  protected $pageSizeType = 'Google_Service_Slides_Size';
  protected $pageSizeDataType = '';
  public $presentationId;
  public $revisionId;
  protected $slidesType = 'Google_Service_Slides_Page';
  protected $slidesDataType = 'array';
  public $title;

  public function setLayouts($layouts)
  {
    $this->layouts = $layouts;
  }
  public function getLayouts()
  {
    return $this->layouts;
  }
  public function setLocale($locale)
  {
    $this->locale = $locale;
  }
  public function getLocale()
  {
    return $this->locale;
  }
  public function setMasters($masters)
  {
    $this->masters = $masters;
  }
  public function getMasters()
  {
    return $this->masters;
  }
  public function setNotesMaster(Google_Service_Slides_Page $notesMaster)
  {
    $this->notesMaster = $notesMaster;
  }
  public function getNotesMaster()
  {
    return $this->notesMaster;
  }
  public function setPageSize(Google_Service_Slides_Size $pageSize)
  {
    $this->pageSize = $pageSize;
  }
  public function getPageSize()
  {
    return $this->pageSize;
  }
  public function setPresentationId($presentationId)
  {
    $this->presentationId = $presentationId;
  }
  public function getPresentationId()
  {
    return $this->presentationId;
  }
  public function setRevisionId($revisionId)
  {
    $this->revisionId = $revisionId;
  }
  public function getRevisionId()
  {
    return $this->revisionId;
  }
  public function setSlides($slides)
  {
    $this->slides = $slides;
  }
  public function getSlides()
  {
    return $this->slides;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}
