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

class Google_Service_AndroidPublisher_InappproductsListResponse extends Google_Collection
{
  protected $collection_key = 'inappproduct';
  protected $inappproductType = 'Google_Service_AndroidPublisher_InAppProduct';
  protected $inappproductDataType = 'array';
  public $kind;
  protected $pageInfoType = 'Google_Service_AndroidPublisher_PageInfo';
  protected $pageInfoDataType = '';
  protected $tokenPaginationType = 'Google_Service_AndroidPublisher_TokenPagination';
  protected $tokenPaginationDataType = '';

  public function setInappproduct($inappproduct)
  {
    $this->inappproduct = $inappproduct;
  }
  public function getInappproduct()
  {
    return $this->inappproduct;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setPageInfo(Google_Service_AndroidPublisher_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setTokenPagination(Google_Service_AndroidPublisher_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
}
