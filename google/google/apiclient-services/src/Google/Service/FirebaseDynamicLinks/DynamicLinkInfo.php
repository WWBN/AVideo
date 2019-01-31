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

class Google_Service_FirebaseDynamicLinks_DynamicLinkInfo extends Google_Model
{
  protected $analyticsInfoType = 'Google_Service_FirebaseDynamicLinks_AnalyticsInfo';
  protected $analyticsInfoDataType = '';
  protected $androidInfoType = 'Google_Service_FirebaseDynamicLinks_AndroidInfo';
  protected $androidInfoDataType = '';
  public $dynamicLinkDomain;
  protected $iosInfoType = 'Google_Service_FirebaseDynamicLinks_IosInfo';
  protected $iosInfoDataType = '';
  public $link;
  protected $navigationInfoType = 'Google_Service_FirebaseDynamicLinks_NavigationInfo';
  protected $navigationInfoDataType = '';
  protected $socialMetaTagInfoType = 'Google_Service_FirebaseDynamicLinks_SocialMetaTagInfo';
  protected $socialMetaTagInfoDataType = '';

  public function setAnalyticsInfo(Google_Service_FirebaseDynamicLinks_AnalyticsInfo $analyticsInfo)
  {
    $this->analyticsInfo = $analyticsInfo;
  }
  public function getAnalyticsInfo()
  {
    return $this->analyticsInfo;
  }
  public function setAndroidInfo(Google_Service_FirebaseDynamicLinks_AndroidInfo $androidInfo)
  {
    $this->androidInfo = $androidInfo;
  }
  public function getAndroidInfo()
  {
    return $this->androidInfo;
  }
  public function setDynamicLinkDomain($dynamicLinkDomain)
  {
    $this->dynamicLinkDomain = $dynamicLinkDomain;
  }
  public function getDynamicLinkDomain()
  {
    return $this->dynamicLinkDomain;
  }
  public function setIosInfo(Google_Service_FirebaseDynamicLinks_IosInfo $iosInfo)
  {
    $this->iosInfo = $iosInfo;
  }
  public function getIosInfo()
  {
    return $this->iosInfo;
  }
  public function setLink($link)
  {
    $this->link = $link;
  }
  public function getLink()
  {
    return $this->link;
  }
  public function setNavigationInfo(Google_Service_FirebaseDynamicLinks_NavigationInfo $navigationInfo)
  {
    $this->navigationInfo = $navigationInfo;
  }
  public function getNavigationInfo()
  {
    return $this->navigationInfo;
  }
  public function setSocialMetaTagInfo(Google_Service_FirebaseDynamicLinks_SocialMetaTagInfo $socialMetaTagInfo)
  {
    $this->socialMetaTagInfo = $socialMetaTagInfo;
  }
  public function getSocialMetaTagInfo()
  {
    return $this->socialMetaTagInfo;
  }
}
