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

class Google_Service_YouTube_Channel extends Google_Model
{
  protected $auditDetailsType = 'Google_Service_YouTube_ChannelAuditDetails';
  protected $auditDetailsDataType = '';
  protected $brandingSettingsType = 'Google_Service_YouTube_ChannelBrandingSettings';
  protected $brandingSettingsDataType = '';
  protected $contentDetailsType = 'Google_Service_YouTube_ChannelContentDetails';
  protected $contentDetailsDataType = '';
  protected $contentOwnerDetailsType = 'Google_Service_YouTube_ChannelContentOwnerDetails';
  protected $contentOwnerDetailsDataType = '';
  protected $conversionPingsType = 'Google_Service_YouTube_ChannelConversionPings';
  protected $conversionPingsDataType = '';
  public $etag;
  public $id;
  protected $invideoPromotionType = 'Google_Service_YouTube_InvideoPromotion';
  protected $invideoPromotionDataType = '';
  public $kind;
  protected $localizationsType = 'Google_Service_YouTube_ChannelLocalization';
  protected $localizationsDataType = 'map';
  protected $snippetType = 'Google_Service_YouTube_ChannelSnippet';
  protected $snippetDataType = '';
  protected $statisticsType = 'Google_Service_YouTube_ChannelStatistics';
  protected $statisticsDataType = '';
  protected $statusType = 'Google_Service_YouTube_ChannelStatus';
  protected $statusDataType = '';
  protected $topicDetailsType = 'Google_Service_YouTube_ChannelTopicDetails';
  protected $topicDetailsDataType = '';

  public function setAuditDetails(Google_Service_YouTube_ChannelAuditDetails $auditDetails)
  {
    $this->auditDetails = $auditDetails;
  }
  public function getAuditDetails()
  {
    return $this->auditDetails;
  }
  public function setBrandingSettings(Google_Service_YouTube_ChannelBrandingSettings $brandingSettings)
  {
    $this->brandingSettings = $brandingSettings;
  }
  public function getBrandingSettings()
  {
    return $this->brandingSettings;
  }
  public function setContentDetails(Google_Service_YouTube_ChannelContentDetails $contentDetails)
  {
    $this->contentDetails = $contentDetails;
  }
  public function getContentDetails()
  {
    return $this->contentDetails;
  }
  public function setContentOwnerDetails(Google_Service_YouTube_ChannelContentOwnerDetails $contentOwnerDetails)
  {
    $this->contentOwnerDetails = $contentOwnerDetails;
  }
  public function getContentOwnerDetails()
  {
    return $this->contentOwnerDetails;
  }
  public function setConversionPings(Google_Service_YouTube_ChannelConversionPings $conversionPings)
  {
    $this->conversionPings = $conversionPings;
  }
  public function getConversionPings()
  {
    return $this->conversionPings;
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
  public function setInvideoPromotion(Google_Service_YouTube_InvideoPromotion $invideoPromotion)
  {
    $this->invideoPromotion = $invideoPromotion;
  }
  public function getInvideoPromotion()
  {
    return $this->invideoPromotion;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLocalizations($localizations)
  {
    $this->localizations = $localizations;
  }
  public function getLocalizations()
  {
    return $this->localizations;
  }
  public function setSnippet(Google_Service_YouTube_ChannelSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
  public function setStatistics(Google_Service_YouTube_ChannelStatistics $statistics)
  {
    $this->statistics = $statistics;
  }
  public function getStatistics()
  {
    return $this->statistics;
  }
  public function setStatus(Google_Service_YouTube_ChannelStatus $status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setTopicDetails(Google_Service_YouTube_ChannelTopicDetails $topicDetails)
  {
    $this->topicDetails = $topicDetails;
  }
  public function getTopicDetails()
  {
    return $this->topicDetails;
  }
}
