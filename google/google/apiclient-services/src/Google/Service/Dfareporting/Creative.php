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

class Google_Service_Dfareporting_Creative extends Google_Collection
{
  protected $collection_key = 'timerCustomEvents';
  protected $internal_gapi_mappings = array(
        "autoAdvanceImages" => "auto_advance_images",
  );
  public $accountId;
  public $active;
  public $adParameters;
  public $adTagKeys;
  public $advertiserId;
  public $allowScriptAccess;
  public $archived;
  public $artworkType;
  public $authoringSource;
  public $authoringTool;
  public $autoAdvanceImages;
  public $backgroundColor;
  public $backupImageClickThroughUrl;
  public $backupImageFeatures;
  public $backupImageReportingLabel;
  protected $backupImageTargetWindowType = 'Google_Service_Dfareporting_TargetWindow';
  protected $backupImageTargetWindowDataType = '';
  protected $clickTagsType = 'Google_Service_Dfareporting_ClickTag';
  protected $clickTagsDataType = 'array';
  public $commercialId;
  public $companionCreatives;
  public $compatibility;
  public $convertFlashToHtml5;
  protected $counterCustomEventsType = 'Google_Service_Dfareporting_CreativeCustomEvent';
  protected $counterCustomEventsDataType = 'array';
  protected $creativeAssetSelectionType = 'Google_Service_Dfareporting_CreativeAssetSelection';
  protected $creativeAssetSelectionDataType = '';
  protected $creativeAssetsType = 'Google_Service_Dfareporting_CreativeAsset';
  protected $creativeAssetsDataType = 'array';
  protected $creativeFieldAssignmentsType = 'Google_Service_Dfareporting_CreativeFieldAssignment';
  protected $creativeFieldAssignmentsDataType = 'array';
  public $customKeyValues;
  public $dynamicAssetSelection;
  protected $exitCustomEventsType = 'Google_Service_Dfareporting_CreativeCustomEvent';
  protected $exitCustomEventsDataType = 'array';
  protected $fsCommandType = 'Google_Service_Dfareporting_FsCommand';
  protected $fsCommandDataType = '';
  public $htmlCode;
  public $htmlCodeLocked;
  public $id;
  protected $idDimensionValueType = 'Google_Service_Dfareporting_DimensionValue';
  protected $idDimensionValueDataType = '';
  public $kind;
  protected $lastModifiedInfoType = 'Google_Service_Dfareporting_LastModifiedInfo';
  protected $lastModifiedInfoDataType = '';
  public $latestTraffickedCreativeId;
  public $name;
  public $overrideCss;
  protected $progressOffsetType = 'Google_Service_Dfareporting_VideoOffset';
  protected $progressOffsetDataType = '';
  public $redirectUrl;
  public $renderingId;
  protected $renderingIdDimensionValueType = 'Google_Service_Dfareporting_DimensionValue';
  protected $renderingIdDimensionValueDataType = '';
  public $requiredFlashPluginVersion;
  public $requiredFlashVersion;
  protected $sizeType = 'Google_Service_Dfareporting_Size';
  protected $sizeDataType = '';
  protected $skipOffsetType = 'Google_Service_Dfareporting_VideoOffset';
  protected $skipOffsetDataType = '';
  public $skippable;
  public $sslCompliant;
  public $sslOverride;
  public $studioAdvertiserId;
  public $studioCreativeId;
  public $studioTraffickedCreativeId;
  public $subaccountId;
  public $thirdPartyBackupImageImpressionsUrl;
  public $thirdPartyRichMediaImpressionsUrl;
  protected $thirdPartyUrlsType = 'Google_Service_Dfareporting_ThirdPartyTrackingUrl';
  protected $thirdPartyUrlsDataType = 'array';
  protected $timerCustomEventsType = 'Google_Service_Dfareporting_CreativeCustomEvent';
  protected $timerCustomEventsDataType = 'array';
  public $totalFileSize;
  public $type;
  public $version;
  public $videoDescription;
  public $videoDuration;

  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setActive($active)
  {
    $this->active = $active;
  }
  public function getActive()
  {
    return $this->active;
  }
  public function setAdParameters($adParameters)
  {
    $this->adParameters = $adParameters;
  }
  public function getAdParameters()
  {
    return $this->adParameters;
  }
  public function setAdTagKeys($adTagKeys)
  {
    $this->adTagKeys = $adTagKeys;
  }
  public function getAdTagKeys()
  {
    return $this->adTagKeys;
  }
  public function setAdvertiserId($advertiserId)
  {
    $this->advertiserId = $advertiserId;
  }
  public function getAdvertiserId()
  {
    return $this->advertiserId;
  }
  public function setAllowScriptAccess($allowScriptAccess)
  {
    $this->allowScriptAccess = $allowScriptAccess;
  }
  public function getAllowScriptAccess()
  {
    return $this->allowScriptAccess;
  }
  public function setArchived($archived)
  {
    $this->archived = $archived;
  }
  public function getArchived()
  {
    return $this->archived;
  }
  public function setArtworkType($artworkType)
  {
    $this->artworkType = $artworkType;
  }
  public function getArtworkType()
  {
    return $this->artworkType;
  }
  public function setAuthoringSource($authoringSource)
  {
    $this->authoringSource = $authoringSource;
  }
  public function getAuthoringSource()
  {
    return $this->authoringSource;
  }
  public function setAuthoringTool($authoringTool)
  {
    $this->authoringTool = $authoringTool;
  }
  public function getAuthoringTool()
  {
    return $this->authoringTool;
  }
  public function setAutoAdvanceImages($autoAdvanceImages)
  {
    $this->autoAdvanceImages = $autoAdvanceImages;
  }
  public function getAutoAdvanceImages()
  {
    return $this->autoAdvanceImages;
  }
  public function setBackgroundColor($backgroundColor)
  {
    $this->backgroundColor = $backgroundColor;
  }
  public function getBackgroundColor()
  {
    return $this->backgroundColor;
  }
  public function setBackupImageClickThroughUrl($backupImageClickThroughUrl)
  {
    $this->backupImageClickThroughUrl = $backupImageClickThroughUrl;
  }
  public function getBackupImageClickThroughUrl()
  {
    return $this->backupImageClickThroughUrl;
  }
  public function setBackupImageFeatures($backupImageFeatures)
  {
    $this->backupImageFeatures = $backupImageFeatures;
  }
  public function getBackupImageFeatures()
  {
    return $this->backupImageFeatures;
  }
  public function setBackupImageReportingLabel($backupImageReportingLabel)
  {
    $this->backupImageReportingLabel = $backupImageReportingLabel;
  }
  public function getBackupImageReportingLabel()
  {
    return $this->backupImageReportingLabel;
  }
  public function setBackupImageTargetWindow(Google_Service_Dfareporting_TargetWindow $backupImageTargetWindow)
  {
    $this->backupImageTargetWindow = $backupImageTargetWindow;
  }
  public function getBackupImageTargetWindow()
  {
    return $this->backupImageTargetWindow;
  }
  public function setClickTags($clickTags)
  {
    $this->clickTags = $clickTags;
  }
  public function getClickTags()
  {
    return $this->clickTags;
  }
  public function setCommercialId($commercialId)
  {
    $this->commercialId = $commercialId;
  }
  public function getCommercialId()
  {
    return $this->commercialId;
  }
  public function setCompanionCreatives($companionCreatives)
  {
    $this->companionCreatives = $companionCreatives;
  }
  public function getCompanionCreatives()
  {
    return $this->companionCreatives;
  }
  public function setCompatibility($compatibility)
  {
    $this->compatibility = $compatibility;
  }
  public function getCompatibility()
  {
    return $this->compatibility;
  }
  public function setConvertFlashToHtml5($convertFlashToHtml5)
  {
    $this->convertFlashToHtml5 = $convertFlashToHtml5;
  }
  public function getConvertFlashToHtml5()
  {
    return $this->convertFlashToHtml5;
  }
  public function setCounterCustomEvents($counterCustomEvents)
  {
    $this->counterCustomEvents = $counterCustomEvents;
  }
  public function getCounterCustomEvents()
  {
    return $this->counterCustomEvents;
  }
  public function setCreativeAssetSelection(Google_Service_Dfareporting_CreativeAssetSelection $creativeAssetSelection)
  {
    $this->creativeAssetSelection = $creativeAssetSelection;
  }
  public function getCreativeAssetSelection()
  {
    return $this->creativeAssetSelection;
  }
  public function setCreativeAssets($creativeAssets)
  {
    $this->creativeAssets = $creativeAssets;
  }
  public function getCreativeAssets()
  {
    return $this->creativeAssets;
  }
  public function setCreativeFieldAssignments($creativeFieldAssignments)
  {
    $this->creativeFieldAssignments = $creativeFieldAssignments;
  }
  public function getCreativeFieldAssignments()
  {
    return $this->creativeFieldAssignments;
  }
  public function setCustomKeyValues($customKeyValues)
  {
    $this->customKeyValues = $customKeyValues;
  }
  public function getCustomKeyValues()
  {
    return $this->customKeyValues;
  }
  public function setDynamicAssetSelection($dynamicAssetSelection)
  {
    $this->dynamicAssetSelection = $dynamicAssetSelection;
  }
  public function getDynamicAssetSelection()
  {
    return $this->dynamicAssetSelection;
  }
  public function setExitCustomEvents($exitCustomEvents)
  {
    $this->exitCustomEvents = $exitCustomEvents;
  }
  public function getExitCustomEvents()
  {
    return $this->exitCustomEvents;
  }
  public function setFsCommand(Google_Service_Dfareporting_FsCommand $fsCommand)
  {
    $this->fsCommand = $fsCommand;
  }
  public function getFsCommand()
  {
    return $this->fsCommand;
  }
  public function setHtmlCode($htmlCode)
  {
    $this->htmlCode = $htmlCode;
  }
  public function getHtmlCode()
  {
    return $this->htmlCode;
  }
  public function setHtmlCodeLocked($htmlCodeLocked)
  {
    $this->htmlCodeLocked = $htmlCodeLocked;
  }
  public function getHtmlCodeLocked()
  {
    return $this->htmlCodeLocked;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setIdDimensionValue(Google_Service_Dfareporting_DimensionValue $idDimensionValue)
  {
    $this->idDimensionValue = $idDimensionValue;
  }
  public function getIdDimensionValue()
  {
    return $this->idDimensionValue;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLastModifiedInfo(Google_Service_Dfareporting_LastModifiedInfo $lastModifiedInfo)
  {
    $this->lastModifiedInfo = $lastModifiedInfo;
  }
  public function getLastModifiedInfo()
  {
    return $this->lastModifiedInfo;
  }
  public function setLatestTraffickedCreativeId($latestTraffickedCreativeId)
  {
    $this->latestTraffickedCreativeId = $latestTraffickedCreativeId;
  }
  public function getLatestTraffickedCreativeId()
  {
    return $this->latestTraffickedCreativeId;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setOverrideCss($overrideCss)
  {
    $this->overrideCss = $overrideCss;
  }
  public function getOverrideCss()
  {
    return $this->overrideCss;
  }
  public function setProgressOffset(Google_Service_Dfareporting_VideoOffset $progressOffset)
  {
    $this->progressOffset = $progressOffset;
  }
  public function getProgressOffset()
  {
    return $this->progressOffset;
  }
  public function setRedirectUrl($redirectUrl)
  {
    $this->redirectUrl = $redirectUrl;
  }
  public function getRedirectUrl()
  {
    return $this->redirectUrl;
  }
  public function setRenderingId($renderingId)
  {
    $this->renderingId = $renderingId;
  }
  public function getRenderingId()
  {
    return $this->renderingId;
  }
  public function setRenderingIdDimensionValue(Google_Service_Dfareporting_DimensionValue $renderingIdDimensionValue)
  {
    $this->renderingIdDimensionValue = $renderingIdDimensionValue;
  }
  public function getRenderingIdDimensionValue()
  {
    return $this->renderingIdDimensionValue;
  }
  public function setRequiredFlashPluginVersion($requiredFlashPluginVersion)
  {
    $this->requiredFlashPluginVersion = $requiredFlashPluginVersion;
  }
  public function getRequiredFlashPluginVersion()
  {
    return $this->requiredFlashPluginVersion;
  }
  public function setRequiredFlashVersion($requiredFlashVersion)
  {
    $this->requiredFlashVersion = $requiredFlashVersion;
  }
  public function getRequiredFlashVersion()
  {
    return $this->requiredFlashVersion;
  }
  public function setSize(Google_Service_Dfareporting_Size $size)
  {
    $this->size = $size;
  }
  public function getSize()
  {
    return $this->size;
  }
  public function setSkipOffset(Google_Service_Dfareporting_VideoOffset $skipOffset)
  {
    $this->skipOffset = $skipOffset;
  }
  public function getSkipOffset()
  {
    return $this->skipOffset;
  }
  public function setSkippable($skippable)
  {
    $this->skippable = $skippable;
  }
  public function getSkippable()
  {
    return $this->skippable;
  }
  public function setSslCompliant($sslCompliant)
  {
    $this->sslCompliant = $sslCompliant;
  }
  public function getSslCompliant()
  {
    return $this->sslCompliant;
  }
  public function setSslOverride($sslOverride)
  {
    $this->sslOverride = $sslOverride;
  }
  public function getSslOverride()
  {
    return $this->sslOverride;
  }
  public function setStudioAdvertiserId($studioAdvertiserId)
  {
    $this->studioAdvertiserId = $studioAdvertiserId;
  }
  public function getStudioAdvertiserId()
  {
    return $this->studioAdvertiserId;
  }
  public function setStudioCreativeId($studioCreativeId)
  {
    $this->studioCreativeId = $studioCreativeId;
  }
  public function getStudioCreativeId()
  {
    return $this->studioCreativeId;
  }
  public function setStudioTraffickedCreativeId($studioTraffickedCreativeId)
  {
    $this->studioTraffickedCreativeId = $studioTraffickedCreativeId;
  }
  public function getStudioTraffickedCreativeId()
  {
    return $this->studioTraffickedCreativeId;
  }
  public function setSubaccountId($subaccountId)
  {
    $this->subaccountId = $subaccountId;
  }
  public function getSubaccountId()
  {
    return $this->subaccountId;
  }
  public function setThirdPartyBackupImageImpressionsUrl($thirdPartyBackupImageImpressionsUrl)
  {
    $this->thirdPartyBackupImageImpressionsUrl = $thirdPartyBackupImageImpressionsUrl;
  }
  public function getThirdPartyBackupImageImpressionsUrl()
  {
    return $this->thirdPartyBackupImageImpressionsUrl;
  }
  public function setThirdPartyRichMediaImpressionsUrl($thirdPartyRichMediaImpressionsUrl)
  {
    $this->thirdPartyRichMediaImpressionsUrl = $thirdPartyRichMediaImpressionsUrl;
  }
  public function getThirdPartyRichMediaImpressionsUrl()
  {
    return $this->thirdPartyRichMediaImpressionsUrl;
  }
  public function setThirdPartyUrls($thirdPartyUrls)
  {
    $this->thirdPartyUrls = $thirdPartyUrls;
  }
  public function getThirdPartyUrls()
  {
    return $this->thirdPartyUrls;
  }
  public function setTimerCustomEvents($timerCustomEvents)
  {
    $this->timerCustomEvents = $timerCustomEvents;
  }
  public function getTimerCustomEvents()
  {
    return $this->timerCustomEvents;
  }
  public function setTotalFileSize($totalFileSize)
  {
    $this->totalFileSize = $totalFileSize;
  }
  public function getTotalFileSize()
  {
    return $this->totalFileSize;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setVersion($version)
  {
    $this->version = $version;
  }
  public function getVersion()
  {
    return $this->version;
  }
  public function setVideoDescription($videoDescription)
  {
    $this->videoDescription = $videoDescription;
  }
  public function getVideoDescription()
  {
    return $this->videoDescription;
  }
  public function setVideoDuration($videoDuration)
  {
    $this->videoDuration = $videoDuration;
  }
  public function getVideoDuration()
  {
    return $this->videoDuration;
  }
}
