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

class QualityNsrNsrData extends \Google\Collection
{
  protected $collection_key = 'versionedData';
  /**
   * @var float
   */
  public $articleScore;
  /**
   * @var float
   */
  public $articleScoreV2;
  /**
   * @var int
   */
  public $chardEncoded;
  /**
   * @var float
   */
  public $chardVariance;
  /**
   * @var int
   */
  public $clusterId;
  protected $clusterUpliftType = QualityNsrNsrDataClusterUplift::class;
  protected $clusterUpliftDataType = '';
  public $clusterUplift;
  /**
   * @var float
   */
  public $clutterScore;
  protected $clutterScoresType = QualityNsrVersionedFloatSignal::class;
  protected $clutterScoresDataType = 'array';
  public $clutterScores;
  /**
   * @var float
   */
  public $directFrac;
  /**
   * @var float
   */
  public $healthScore;
  /**
   * @var string
   */
  public $host;
  /**
   * @var int
   */
  public $i18nBucket;
  /**
   * @var float
   */
  public $impressions;
  /**
   * @var bool
   */
  public $isCovidLocalAuthority;
  /**
   * @var bool
   */
  public $isElectionAuthority;
  /**
   * @var bool
   */
  public $isVideoFocusedSite;
  /**
   * @var int
   */
  public $language;
  /**
   * @var int
   */
  public $largeOrgId;
  /**
   * @var float
   */
  public $localityScore;
  protected $metadataType = QualityNsrNsrDataMetadata::class;
  protected $metadataDataType = '';
  public $metadata;
  /**
   * @var float
   */
  public $newNsr;
  /**
   * @var float
   */
  public $nsr;
  /**
   * @var string
   */
  public $nsrEpoch;
  /**
   * @var float
   */
  public $nsrOverrideBid;
  /**
   * @var float
   */
  public $nsrVariance;
  /**
   * @var bool
   */
  public $nsrdataFromFallbackPatternKey;
  /**
   * @var float
   */
  public $pnav;
  protected $priorAdjustedNsrType = QualityNsrVersionedFloatSignal::class;
  protected $priorAdjustedNsrDataType = 'array';
  public $priorAdjustedNsr;
  /**
   * @var string
   */
  public $secondarySiteChunk;
  /**
   * @var float
   */
  public $shoppingScore;
  /**
   * @var float
   */
  public $siteAutopilotScore;
  /**
   * @var string
   */
  public $siteChunk;
  /**
   * @var string
   */
  public $siteChunkSource;
  /**
   * @var float
   */
  public $siteLinkIn;
  /**
   * @var float
   */
  public $siteLinkOut;
  /**
   * @var float
   */
  public $sitePr;
  /**
   * @var float
   */
  public $siteQualityStddev;
  /**
   * @var float
   */
  public $spambrainLavcScore;
  protected $spambrainLavcScoresType = QualityNsrVersionedFloatSignal::class;
  protected $spambrainLavcScoresDataType = 'array';
  public $spambrainLavcScores;
  /**
   * @var float
   */
  public $tofu;
  /**
   * @var float
   */
  public $ugcScore;
  /**
   * @var string
   */
  public $url;
  protected $versionedAsrDataType = QualityNsrVersionedFloatSignal::class;
  protected $versionedAsrDataDataType = 'array';
  public $versionedAsrData;
  protected $versionedDataType = QualityNsrNSRVersionedData::class;
  protected $versionedDataDataType = 'array';
  public $versionedData;
  /**
   * @var float
   */
  public $videoScore;
  /**
   * @var float
   */
  public $vlq;
  /**
   * @var float
   */
  public $vlqNsr;
  /**
   * @var float
   */
  public $ymylNewsV2Score;

  /**
   * @param float
   */
  public function setArticleScore($articleScore)
  {
    $this->articleScore = $articleScore;
  }
  /**
   * @return float
   */
  public function getArticleScore()
  {
    return $this->articleScore;
  }
  /**
   * @param float
   */
  public function setArticleScoreV2($articleScoreV2)
  {
    $this->articleScoreV2 = $articleScoreV2;
  }
  /**
   * @return float
   */
  public function getArticleScoreV2()
  {
    return $this->articleScoreV2;
  }
  /**
   * @param int
   */
  public function setChardEncoded($chardEncoded)
  {
    $this->chardEncoded = $chardEncoded;
  }
  /**
   * @return int
   */
  public function getChardEncoded()
  {
    return $this->chardEncoded;
  }
  /**
   * @param float
   */
  public function setChardVariance($chardVariance)
  {
    $this->chardVariance = $chardVariance;
  }
  /**
   * @return float
   */
  public function getChardVariance()
  {
    return $this->chardVariance;
  }
  /**
   * @param int
   */
  public function setClusterId($clusterId)
  {
    $this->clusterId = $clusterId;
  }
  /**
   * @return int
   */
  public function getClusterId()
  {
    return $this->clusterId;
  }
  /**
   * @param QualityNsrNsrDataClusterUplift
   */
  public function setClusterUplift(QualityNsrNsrDataClusterUplift $clusterUplift)
  {
    $this->clusterUplift = $clusterUplift;
  }
  /**
   * @return QualityNsrNsrDataClusterUplift
   */
  public function getClusterUplift()
  {
    return $this->clusterUplift;
  }
  /**
   * @param float
   */
  public function setClutterScore($clutterScore)
  {
    $this->clutterScore = $clutterScore;
  }
  /**
   * @return float
   */
  public function getClutterScore()
  {
    return $this->clutterScore;
  }
  /**
   * @param QualityNsrVersionedFloatSignal[]
   */
  public function setClutterScores($clutterScores)
  {
    $this->clutterScores = $clutterScores;
  }
  /**
   * @return QualityNsrVersionedFloatSignal[]
   */
  public function getClutterScores()
  {
    return $this->clutterScores;
  }
  /**
   * @param float
   */
  public function setDirectFrac($directFrac)
  {
    $this->directFrac = $directFrac;
  }
  /**
   * @return float
   */
  public function getDirectFrac()
  {
    return $this->directFrac;
  }
  /**
   * @param float
   */
  public function setHealthScore($healthScore)
  {
    $this->healthScore = $healthScore;
  }
  /**
   * @return float
   */
  public function getHealthScore()
  {
    return $this->healthScore;
  }
  /**
   * @param string
   */
  public function setHost($host)
  {
    $this->host = $host;
  }
  /**
   * @return string
   */
  public function getHost()
  {
    return $this->host;
  }
  /**
   * @param int
   */
  public function setI18nBucket($i18nBucket)
  {
    $this->i18nBucket = $i18nBucket;
  }
  /**
   * @return int
   */
  public function getI18nBucket()
  {
    return $this->i18nBucket;
  }
  /**
   * @param float
   */
  public function setImpressions($impressions)
  {
    $this->impressions = $impressions;
  }
  /**
   * @return float
   */
  public function getImpressions()
  {
    return $this->impressions;
  }
  /**
   * @param bool
   */
  public function setIsCovidLocalAuthority($isCovidLocalAuthority)
  {
    $this->isCovidLocalAuthority = $isCovidLocalAuthority;
  }
  /**
   * @return bool
   */
  public function getIsCovidLocalAuthority()
  {
    return $this->isCovidLocalAuthority;
  }
  /**
   * @param bool
   */
  public function setIsElectionAuthority($isElectionAuthority)
  {
    $this->isElectionAuthority = $isElectionAuthority;
  }
  /**
   * @return bool
   */
  public function getIsElectionAuthority()
  {
    return $this->isElectionAuthority;
  }
  /**
   * @param bool
   */
  public function setIsVideoFocusedSite($isVideoFocusedSite)
  {
    $this->isVideoFocusedSite = $isVideoFocusedSite;
  }
  /**
   * @return bool
   */
  public function getIsVideoFocusedSite()
  {
    return $this->isVideoFocusedSite;
  }
  /**
   * @param int
   */
  public function setLanguage($language)
  {
    $this->language = $language;
  }
  /**
   * @return int
   */
  public function getLanguage()
  {
    return $this->language;
  }
  /**
   * @param int
   */
  public function setLargeOrgId($largeOrgId)
  {
    $this->largeOrgId = $largeOrgId;
  }
  /**
   * @return int
   */
  public function getLargeOrgId()
  {
    return $this->largeOrgId;
  }
  /**
   * @param float
   */
  public function setLocalityScore($localityScore)
  {
    $this->localityScore = $localityScore;
  }
  /**
   * @return float
   */
  public function getLocalityScore()
  {
    return $this->localityScore;
  }
  /**
   * @param QualityNsrNsrDataMetadata
   */
  public function setMetadata(QualityNsrNsrDataMetadata $metadata)
  {
    $this->metadata = $metadata;
  }
  /**
   * @return QualityNsrNsrDataMetadata
   */
  public function getMetadata()
  {
    return $this->metadata;
  }
  /**
   * @param float
   */
  public function setNewNsr($newNsr)
  {
    $this->newNsr = $newNsr;
  }
  /**
   * @return float
   */
  public function getNewNsr()
  {
    return $this->newNsr;
  }
  /**
   * @param float
   */
  public function setNsr($nsr)
  {
    $this->nsr = $nsr;
  }
  /**
   * @return float
   */
  public function getNsr()
  {
    return $this->nsr;
  }
  /**
   * @param string
   */
  public function setNsrEpoch($nsrEpoch)
  {
    $this->nsrEpoch = $nsrEpoch;
  }
  /**
   * @return string
   */
  public function getNsrEpoch()
  {
    return $this->nsrEpoch;
  }
  /**
   * @param float
   */
  public function setNsrOverrideBid($nsrOverrideBid)
  {
    $this->nsrOverrideBid = $nsrOverrideBid;
  }
  /**
   * @return float
   */
  public function getNsrOverrideBid()
  {
    return $this->nsrOverrideBid;
  }
  /**
   * @param float
   */
  public function setNsrVariance($nsrVariance)
  {
    $this->nsrVariance = $nsrVariance;
  }
  /**
   * @return float
   */
  public function getNsrVariance()
  {
    return $this->nsrVariance;
  }
  /**
   * @param bool
   */
  public function setNsrdataFromFallbackPatternKey($nsrdataFromFallbackPatternKey)
  {
    $this->nsrdataFromFallbackPatternKey = $nsrdataFromFallbackPatternKey;
  }
  /**
   * @return bool
   */
  public function getNsrdataFromFallbackPatternKey()
  {
    return $this->nsrdataFromFallbackPatternKey;
  }
  /**
   * @param float
   */
  public function setPnav($pnav)
  {
    $this->pnav = $pnav;
  }
  /**
   * @return float
   */
  public function getPnav()
  {
    return $this->pnav;
  }
  /**
   * @param QualityNsrVersionedFloatSignal[]
   */
  public function setPriorAdjustedNsr($priorAdjustedNsr)
  {
    $this->priorAdjustedNsr = $priorAdjustedNsr;
  }
  /**
   * @return QualityNsrVersionedFloatSignal[]
   */
  public function getPriorAdjustedNsr()
  {
    return $this->priorAdjustedNsr;
  }
  /**
   * @param string
   */
  public function setSecondarySiteChunk($secondarySiteChunk)
  {
    $this->secondarySiteChunk = $secondarySiteChunk;
  }
  /**
   * @return string
   */
  public function getSecondarySiteChunk()
  {
    return $this->secondarySiteChunk;
  }
  /**
   * @param float
   */
  public function setShoppingScore($shoppingScore)
  {
    $this->shoppingScore = $shoppingScore;
  }
  /**
   * @return float
   */
  public function getShoppingScore()
  {
    return $this->shoppingScore;
  }
  /**
   * @param float
   */
  public function setSiteAutopilotScore($siteAutopilotScore)
  {
    $this->siteAutopilotScore = $siteAutopilotScore;
  }
  /**
   * @return float
   */
  public function getSiteAutopilotScore()
  {
    return $this->siteAutopilotScore;
  }
  /**
   * @param string
   */
  public function setSiteChunk($siteChunk)
  {
    $this->siteChunk = $siteChunk;
  }
  /**
   * @return string
   */
  public function getSiteChunk()
  {
    return $this->siteChunk;
  }
  /**
   * @param string
   */
  public function setSiteChunkSource($siteChunkSource)
  {
    $this->siteChunkSource = $siteChunkSource;
  }
  /**
   * @return string
   */
  public function getSiteChunkSource()
  {
    return $this->siteChunkSource;
  }
  /**
   * @param float
   */
  public function setSiteLinkIn($siteLinkIn)
  {
    $this->siteLinkIn = $siteLinkIn;
  }
  /**
   * @return float
   */
  public function getSiteLinkIn()
  {
    return $this->siteLinkIn;
  }
  /**
   * @param float
   */
  public function setSiteLinkOut($siteLinkOut)
  {
    $this->siteLinkOut = $siteLinkOut;
  }
  /**
   * @return float
   */
  public function getSiteLinkOut()
  {
    return $this->siteLinkOut;
  }
  /**
   * @param float
   */
  public function setSitePr($sitePr)
  {
    $this->sitePr = $sitePr;
  }
  /**
   * @return float
   */
  public function getSitePr()
  {
    return $this->sitePr;
  }
  /**
   * @param float
   */
  public function setSiteQualityStddev($siteQualityStddev)
  {
    $this->siteQualityStddev = $siteQualityStddev;
  }
  /**
   * @return float
   */
  public function getSiteQualityStddev()
  {
    return $this->siteQualityStddev;
  }
  /**
   * @param float
   */
  public function setSpambrainLavcScore($spambrainLavcScore)
  {
    $this->spambrainLavcScore = $spambrainLavcScore;
  }
  /**
   * @return float
   */
  public function getSpambrainLavcScore()
  {
    return $this->spambrainLavcScore;
  }
  /**
   * @param QualityNsrVersionedFloatSignal[]
   */
  public function setSpambrainLavcScores($spambrainLavcScores)
  {
    $this->spambrainLavcScores = $spambrainLavcScores;
  }
  /**
   * @return QualityNsrVersionedFloatSignal[]
   */
  public function getSpambrainLavcScores()
  {
    return $this->spambrainLavcScores;
  }
  /**
   * @param float
   */
  public function setTofu($tofu)
  {
    $this->tofu = $tofu;
  }
  /**
   * @return float
   */
  public function getTofu()
  {
    return $this->tofu;
  }
  /**
   * @param float
   */
  public function setUgcScore($ugcScore)
  {
    $this->ugcScore = $ugcScore;
  }
  /**
   * @return float
   */
  public function getUgcScore()
  {
    return $this->ugcScore;
  }
  /**
   * @param string
   */
  public function setUrl($url)
  {
    $this->url = $url;
  }
  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }
  /**
   * @param QualityNsrVersionedFloatSignal[]
   */
  public function setVersionedAsrData($versionedAsrData)
  {
    $this->versionedAsrData = $versionedAsrData;
  }
  /**
   * @return QualityNsrVersionedFloatSignal[]
   */
  public function getVersionedAsrData()
  {
    return $this->versionedAsrData;
  }
  /**
   * @param QualityNsrNSRVersionedData[]
   */
  public function setVersionedData($versionedData)
  {
    $this->versionedData = $versionedData;
  }
  /**
   * @return QualityNsrNSRVersionedData[]
   */
  public function getVersionedData()
  {
    return $this->versionedData;
  }
  /**
   * @param float
   */
  public function setVideoScore($videoScore)
  {
    $this->videoScore = $videoScore;
  }
  /**
   * @return float
   */
  public function getVideoScore()
  {
    return $this->videoScore;
  }
  /**
   * @param float
   */
  public function setVlq($vlq)
  {
    $this->vlq = $vlq;
  }
  /**
   * @return float
   */
  public function getVlq()
  {
    return $this->vlq;
  }
  /**
   * @param float
   */
  public function setVlqNsr($vlqNsr)
  {
    $this->vlqNsr = $vlqNsr;
  }
  /**
   * @return float
   */
  public function getVlqNsr()
  {
    return $this->vlqNsr;
  }
  /**
   * @param float
   */
  public function setYmylNewsV2Score($ymylNewsV2Score)
  {
    $this->ymylNewsV2Score = $ymylNewsV2Score;
  }
  /**
   * @return float
   */
  public function getYmylNewsV2Score()
  {
    return $this->ymylNewsV2Score;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(QualityNsrNsrData::class, 'Google_Service_Contentwarehouse_QualityNsrNsrData');
