<?php
/*
 * Copyright 2014 Google Inc.
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

namespace Google\Service\Contentwarehouse;

class CompositeDocIndexingInfo extends \Google\Collection
{
  protected $collection_key = 'verticals';
  protected $cdocBuildInfoType = IndexingDocjoinerCDocBuildInfo::class;
  protected $cdocBuildInfoDataType = '';
  /**
   * @var bool
   */
  public $contentProtected;
  /**
   * @var int
   */
  public $convertToRobotedReason;
  /**
   * @var int
   */
  public $crawlStatus;
  /**
   * @var string[]
   */
  public $demotionTags;
  /**
   * @var int
   */
  public $errorType;
  /**
   * @var string[]
   */
  public $freshdocsCorpora;
  /**
   * @var string
   */
  public $hostid;
  /**
   * @var string
   */
  public $ieIdentifier;
  protected $imageIndexingInfoType = ImageSearchImageIndexingInfo::class;
  protected $imageIndexingInfoDataType = '';
  /**
   * @var string
   */
  public $indexingTs;
  /**
   * @var string
   */
  public $noLongerCanonicalTimestamp;
  /**
   * @var float
   */
  public $normalizedClickScore;
  /**
   * @var string
   */
  public $primaryVertical;
  /**
   * @var int
   */
  public $rawNavboost;
  /**
   * @var string
   */
  public $rowTimestamp;
  /**
   * @var float
   */
  public $selectionTierRank;
  /**
   * @var string[]
   */
  public $tracingId;
  protected $urlChangerateType = CrawlerChangerateUrlChangerate::class;
  protected $urlChangerateDataType = '';
  protected $urlHistoryType = CrawlerChangerateUrlHistory::class;
  protected $urlHistoryDataType = '';
  protected $urlPatternSignalsType = IndexingSignalAggregatorUrlPatternSignals::class;
  protected $urlPatternSignalsDataType = '';
  /**
   * @var string[]
   */
  public $verticals;
  protected $videoIndexingInfoType = ImageRepositoryVideoIndexingInfo::class;
  protected $videoIndexingInfoDataType = '';

  /**
   * @param IndexingDocjoinerCDocBuildInfo
   */
  public function setCdocBuildInfo(IndexingDocjoinerCDocBuildInfo $cdocBuildInfo)
  {
    $this->cdocBuildInfo = $cdocBuildInfo;
  }
  /**
   * @return IndexingDocjoinerCDocBuildInfo
   */
  public function getCdocBuildInfo()
  {
    return $this->cdocBuildInfo;
  }
  /**
   * @param bool
   */
  public function setContentProtected($contentProtected)
  {
    $this->contentProtected = $contentProtected;
  }
  /**
   * @return bool
   */
  public function getContentProtected()
  {
    return $this->contentProtected;
  }
  /**
   * @param int
   */
  public function setConvertToRobotedReason($convertToRobotedReason)
  {
    $this->convertToRobotedReason = $convertToRobotedReason;
  }
  /**
   * @return int
   */
  public function getConvertToRobotedReason()
  {
    return $this->convertToRobotedReason;
  }
  /**
   * @param int
   */
  public function setCrawlStatus($crawlStatus)
  {
    $this->crawlStatus = $crawlStatus;
  }
  /**
   * @return int
   */
  public function getCrawlStatus()
  {
    return $this->crawlStatus;
  }
  /**
   * @param string[]
   */
  public function setDemotionTags($demotionTags)
  {
    $this->demotionTags = $demotionTags;
  }
  /**
   * @return string[]
   */
  public function getDemotionTags()
  {
    return $this->demotionTags;
  }
  /**
   * @param int
   */
  public function setErrorType($errorType)
  {
    $this->errorType = $errorType;
  }
  /**
   * @return int
   */
  public function getErrorType()
  {
    return $this->errorType;
  }
  /**
   * @param string[]
   */
  public function setFreshdocsCorpora($freshdocsCorpora)
  {
    $this->freshdocsCorpora = $freshdocsCorpora;
  }
  /**
   * @return string[]
   */
  public function getFreshdocsCorpora()
  {
    return $this->freshdocsCorpora;
  }
  /**
   * @param string
   */
  public function setHostid($hostid)
  {
    $this->hostid = $hostid;
  }
  /**
   * @return string
   */
  public function getHostid()
  {
    return $this->hostid;
  }
  /**
   * @param string
   */
  public function setIeIdentifier($ieIdentifier)
  {
    $this->ieIdentifier = $ieIdentifier;
  }
  /**
   * @return string
   */
  public function getIeIdentifier()
  {
    return $this->ieIdentifier;
  }
  /**
   * @param ImageSearchImageIndexingInfo
   */
  public function setImageIndexingInfo(ImageSearchImageIndexingInfo $imageIndexingInfo)
  {
    $this->imageIndexingInfo = $imageIndexingInfo;
  }
  /**
   * @return ImageSearchImageIndexingInfo
   */
  public function getImageIndexingInfo()
  {
    return $this->imageIndexingInfo;
  }
  /**
   * @param string
   */
  public function setIndexingTs($indexingTs)
  {
    $this->indexingTs = $indexingTs;
  }
  /**
   * @return string
   */
  public function getIndexingTs()
  {
    return $this->indexingTs;
  }
  /**
   * @param string
   */
  public function setNoLongerCanonicalTimestamp($noLongerCanonicalTimestamp)
  {
    $this->noLongerCanonicalTimestamp = $noLongerCanonicalTimestamp;
  }
  /**
   * @return string
   */
  public function getNoLongerCanonicalTimestamp()
  {
    return $this->noLongerCanonicalTimestamp;
  }
  /**
   * @param float
   */
  public function setNormalizedClickScore($normalizedClickScore)
  {
    $this->normalizedClickScore = $normalizedClickScore;
  }
  /**
   * @return float
   */
  public function getNormalizedClickScore()
  {
    return $this->normalizedClickScore;
  }
  /**
   * @param string
   */
  public function setPrimaryVertical($primaryVertical)
  {
    $this->primaryVertical = $primaryVertical;
  }
  /**
   * @return string
   */
  public function getPrimaryVertical()
  {
    return $this->primaryVertical;
  }
  /**
   * @param int
   */
  public function setRawNavboost($rawNavboost)
  {
    $this->rawNavboost = $rawNavboost;
  }
  /**
   * @return int
   */
  public function getRawNavboost()
  {
    return $this->rawNavboost;
  }
  /**
   * @param string
   */
  public function setRowTimestamp($rowTimestamp)
  {
    $this->rowTimestamp = $rowTimestamp;
  }
  /**
   * @return string
   */
  public function getRowTimestamp()
  {
    return $this->rowTimestamp;
  }
  /**
   * @param float
   */
  public function setSelectionTierRank($selectionTierRank)
  {
    $this->selectionTierRank = $selectionTierRank;
  }
  /**
   * @return float
   */
  public function getSelectionTierRank()
  {
    return $this->selectionTierRank;
  }
  /**
   * @param string[]
   */
  public function setTracingId($tracingId)
  {
    $this->tracingId = $tracingId;
  }
  /**
   * @return string[]
   */
  public function getTracingId()
  {
    return $this->tracingId;
  }
  /**
   * @param CrawlerChangerateUrlChangerate
   */
  public function setUrlChangerate(CrawlerChangerateUrlChangerate $urlChangerate)
  {
    $this->urlChangerate = $urlChangerate;
  }
  /**
   * @return CrawlerChangerateUrlChangerate
   */
  public function getUrlChangerate()
  {
    return $this->urlChangerate;
  }
  /**
   * @param CrawlerChangerateUrlHistory
   */
  public function setUrlHistory(CrawlerChangerateUrlHistory $urlHistory)
  {
    $this->urlHistory = $urlHistory;
  }
  /**
   * @return CrawlerChangerateUrlHistory
   */
  public function getUrlHistory()
  {
    return $this->urlHistory;
  }
  /**
   * @param IndexingSignalAggregatorUrlPatternSignals
   */
  public function setUrlPatternSignals(IndexingSignalAggregatorUrlPatternSignals $urlPatternSignals)
  {
    $this->urlPatternSignals = $urlPatternSignals;
  }
  /**
   * @return IndexingSignalAggregatorUrlPatternSignals
   */
  public function getUrlPatternSignals()
  {
    return $this->urlPatternSignals;
  }
  /**
   * @param string[]
   */
  public function setVerticals($verticals)
  {
    $this->verticals = $verticals;
  }
  /**
   * @return string[]
   */
  public function getVerticals()
  {
    return $this->verticals;
  }
  /**
   * @param ImageRepositoryVideoIndexingInfo
   */
  public function setVideoIndexingInfo(ImageRepositoryVideoIndexingInfo $videoIndexingInfo)
  {
    $this->videoIndexingInfo = $videoIndexingInfo;
  }
  /**
   * @return ImageRepositoryVideoIndexingInfo
   */
  public function getVideoIndexingInfo()
  {
    return $this->videoIndexingInfo;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CompositeDocIndexingInfo::class, 'Google_Service_Contentwarehouse_CompositeDocIndexingInfo');
