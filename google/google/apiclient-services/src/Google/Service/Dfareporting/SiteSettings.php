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

class Google_Service_Dfareporting_SiteSettings extends Google_Model
{
  public $activeViewOptOut;
  protected $creativeSettingsType = 'Google_Service_Dfareporting_CreativeSettings';
  protected $creativeSettingsDataType = '';
  public $disableBrandSafeAds;
  public $disableNewCookie;
  protected $lookbackConfigurationType = 'Google_Service_Dfareporting_LookbackConfiguration';
  protected $lookbackConfigurationDataType = '';
  protected $tagSettingType = 'Google_Service_Dfareporting_TagSetting';
  protected $tagSettingDataType = '';
  public $videoActiveViewOptOutTemplate;
  public $vpaidAdapterChoiceTemplate;

  public function setActiveViewOptOut($activeViewOptOut)
  {
    $this->activeViewOptOut = $activeViewOptOut;
  }
  public function getActiveViewOptOut()
  {
    return $this->activeViewOptOut;
  }
  public function setCreativeSettings(Google_Service_Dfareporting_CreativeSettings $creativeSettings)
  {
    $this->creativeSettings = $creativeSettings;
  }
  public function getCreativeSettings()
  {
    return $this->creativeSettings;
  }
  public function setDisableBrandSafeAds($disableBrandSafeAds)
  {
    $this->disableBrandSafeAds = $disableBrandSafeAds;
  }
  public function getDisableBrandSafeAds()
  {
    return $this->disableBrandSafeAds;
  }
  public function setDisableNewCookie($disableNewCookie)
  {
    $this->disableNewCookie = $disableNewCookie;
  }
  public function getDisableNewCookie()
  {
    return $this->disableNewCookie;
  }
  public function setLookbackConfiguration(Google_Service_Dfareporting_LookbackConfiguration $lookbackConfiguration)
  {
    $this->lookbackConfiguration = $lookbackConfiguration;
  }
  public function getLookbackConfiguration()
  {
    return $this->lookbackConfiguration;
  }
  public function setTagSetting(Google_Service_Dfareporting_TagSetting $tagSetting)
  {
    $this->tagSetting = $tagSetting;
  }
  public function getTagSetting()
  {
    return $this->tagSetting;
  }
  public function setVideoActiveViewOptOutTemplate($videoActiveViewOptOutTemplate)
  {
    $this->videoActiveViewOptOutTemplate = $videoActiveViewOptOutTemplate;
  }
  public function getVideoActiveViewOptOutTemplate()
  {
    return $this->videoActiveViewOptOutTemplate;
  }
  public function setVpaidAdapterChoiceTemplate($vpaidAdapterChoiceTemplate)
  {
    $this->vpaidAdapterChoiceTemplate = $vpaidAdapterChoiceTemplate;
  }
  public function getVpaidAdapterChoiceTemplate()
  {
    return $this->vpaidAdapterChoiceTemplate;
  }
}
