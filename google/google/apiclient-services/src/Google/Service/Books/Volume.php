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

class Google_Service_Books_Volume extends Google_Model
{
  protected $accessInfoType = 'Google_Service_Books_VolumeAccessInfo';
  protected $accessInfoDataType = '';
  public $etag;
  public $id;
  public $kind;
  protected $layerInfoType = 'Google_Service_Books_VolumeLayerInfo';
  protected $layerInfoDataType = '';
  protected $recommendedInfoType = 'Google_Service_Books_VolumeRecommendedInfo';
  protected $recommendedInfoDataType = '';
  protected $saleInfoType = 'Google_Service_Books_VolumeSaleInfo';
  protected $saleInfoDataType = '';
  protected $searchInfoType = 'Google_Service_Books_VolumeSearchInfo';
  protected $searchInfoDataType = '';
  public $selfLink;
  protected $userInfoType = 'Google_Service_Books_VolumeUserInfo';
  protected $userInfoDataType = '';
  protected $volumeInfoType = 'Google_Service_Books_VolumeVolumeInfo';
  protected $volumeInfoDataType = '';

  public function setAccessInfo(Google_Service_Books_VolumeAccessInfo $accessInfo)
  {
    $this->accessInfo = $accessInfo;
  }
  public function getAccessInfo()
  {
    return $this->accessInfo;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLayerInfo(Google_Service_Books_VolumeLayerInfo $layerInfo)
  {
    $this->layerInfo = $layerInfo;
  }
  public function getLayerInfo()
  {
    return $this->layerInfo;
  }
  public function setRecommendedInfo(Google_Service_Books_VolumeRecommendedInfo $recommendedInfo)
  {
    $this->recommendedInfo = $recommendedInfo;
  }
  public function getRecommendedInfo()
  {
    return $this->recommendedInfo;
  }
  public function setSaleInfo(Google_Service_Books_VolumeSaleInfo $saleInfo)
  {
    $this->saleInfo = $saleInfo;
  }
  public function getSaleInfo()
  {
    return $this->saleInfo;
  }
  public function setSearchInfo(Google_Service_Books_VolumeSearchInfo $searchInfo)
  {
    $this->searchInfo = $searchInfo;
  }
  public function getSearchInfo()
  {
    return $this->searchInfo;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setUserInfo(Google_Service_Books_VolumeUserInfo $userInfo)
  {
    $this->userInfo = $userInfo;
  }
  public function getUserInfo()
  {
    return $this->userInfo;
  }
  public function setVolumeInfo(Google_Service_Books_VolumeVolumeInfo $volumeInfo)
  {
    $this->volumeInfo = $volumeInfo;
  }
  public function getVolumeInfo()
  {
    return $this->volumeInfo;
  }
}
