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

class Google_Service_YouTube_Video extends Google_Model
{
  protected $ageGatingType = 'Google_Service_YouTube_VideoAgeGating';
  protected $ageGatingDataType = '';
  protected $contentDetailsType = 'Google_Service_YouTube_VideoContentDetails';
  protected $contentDetailsDataType = '';
  public $etag;
  protected $fileDetailsType = 'Google_Service_YouTube_VideoFileDetails';
  protected $fileDetailsDataType = '';
  public $id;
  public $kind;
  protected $liveStreamingDetailsType = 'Google_Service_YouTube_VideoLiveStreamingDetails';
  protected $liveStreamingDetailsDataType = '';
  protected $localizationsType = 'Google_Service_YouTube_VideoLocalization';
  protected $localizationsDataType = 'map';
  protected $monetizationDetailsType = 'Google_Service_YouTube_VideoMonetizationDetails';
  protected $monetizationDetailsDataType = '';
  protected $playerType = 'Google_Service_YouTube_VideoPlayer';
  protected $playerDataType = '';
  protected $processingDetailsType = 'Google_Service_YouTube_VideoProcessingDetails';
  protected $processingDetailsDataType = '';
  protected $projectDetailsType = 'Google_Service_YouTube_VideoProjectDetails';
  protected $projectDetailsDataType = '';
  protected $recordingDetailsType = 'Google_Service_YouTube_VideoRecordingDetails';
  protected $recordingDetailsDataType = '';
  protected $snippetType = 'Google_Service_YouTube_VideoSnippet';
  protected $snippetDataType = '';
  protected $statisticsType = 'Google_Service_YouTube_VideoStatistics';
  protected $statisticsDataType = '';
  protected $statusType = 'Google_Service_YouTube_VideoStatus';
  protected $statusDataType = '';
  protected $suggestionsType = 'Google_Service_YouTube_VideoSuggestions';
  protected $suggestionsDataType = '';
  protected $topicDetailsType = 'Google_Service_YouTube_VideoTopicDetails';
  protected $topicDetailsDataType = '';

  public function setAgeGating(Google_Service_YouTube_VideoAgeGating $ageGating)
  {
    $this->ageGating = $ageGating;
  }
  public function getAgeGating()
  {
    return $this->ageGating;
  }
  public function setContentDetails(Google_Service_YouTube_VideoContentDetails $contentDetails)
  {
    $this->contentDetails = $contentDetails;
  }
  public function getContentDetails()
  {
    return $this->contentDetails;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setFileDetails(Google_Service_YouTube_VideoFileDetails $fileDetails)
  {
    $this->fileDetails = $fileDetails;
  }
  public function getFileDetails()
  {
    return $this->fileDetails;
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
  public function setLiveStreamingDetails(Google_Service_YouTube_VideoLiveStreamingDetails $liveStreamingDetails)
  {
    $this->liveStreamingDetails = $liveStreamingDetails;
  }
  public function getLiveStreamingDetails()
  {
    return $this->liveStreamingDetails;
  }
  public function setLocalizations($localizations)
  {
    $this->localizations = $localizations;
  }
  public function getLocalizations()
  {
    return $this->localizations;
  }
  public function setMonetizationDetails(Google_Service_YouTube_VideoMonetizationDetails $monetizationDetails)
  {
    $this->monetizationDetails = $monetizationDetails;
  }
  public function getMonetizationDetails()
  {
    return $this->monetizationDetails;
  }
  public function setPlayer(Google_Service_YouTube_VideoPlayer $player)
  {
    $this->player = $player;
  }
  public function getPlayer()
  {
    return $this->player;
  }
  public function setProcessingDetails(Google_Service_YouTube_VideoProcessingDetails $processingDetails)
  {
    $this->processingDetails = $processingDetails;
  }
  public function getProcessingDetails()
  {
    return $this->processingDetails;
  }
  public function setProjectDetails(Google_Service_YouTube_VideoProjectDetails $projectDetails)
  {
    $this->projectDetails = $projectDetails;
  }
  public function getProjectDetails()
  {
    return $this->projectDetails;
  }
  public function setRecordingDetails(Google_Service_YouTube_VideoRecordingDetails $recordingDetails)
  {
    $this->recordingDetails = $recordingDetails;
  }
  public function getRecordingDetails()
  {
    return $this->recordingDetails;
  }
  public function setSnippet(Google_Service_YouTube_VideoSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
  public function setStatistics(Google_Service_YouTube_VideoStatistics $statistics)
  {
    $this->statistics = $statistics;
  }
  public function getStatistics()
  {
    return $this->statistics;
  }
  public function setStatus(Google_Service_YouTube_VideoStatus $status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setSuggestions(Google_Service_YouTube_VideoSuggestions $suggestions)
  {
    $this->suggestions = $suggestions;
  }
  public function getSuggestions()
  {
    return $this->suggestions;
  }
  public function setTopicDetails(Google_Service_YouTube_VideoTopicDetails $topicDetails)
  {
    $this->topicDetails = $topicDetails;
  }
  public function getTopicDetails()
  {
    return $this->topicDetails;
  }
}
