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

class PornFlagData extends \Google\Collection
{
  protected $collection_key = 'debugInfo';
  protected $coclickBrainScoresType = ImageSafesearchContentBrainPornAnnotation::class;
  protected $coclickBrainScoresDataType = '';
  public $coclickBrainScores;
  /**
   * @var float
   */
  public $csaiScore;
  protected $debugInfoType = ImagePornDebugInfo::class;
  protected $debugInfoDataType = 'array';
  public $debugInfo;
  /**
   * @var float
   */
  public $finalOffensiveScore;
  /**
   * @var float
   */
  public $finalViolenceScore;
  /**
   * @var string
   */
  public $finalViolenceScoreVersion;
  protected $internalSignalsType = SafesearchInternalImageSignals::class;
  protected $internalSignalsDataType = '';
  public $internalSignals;
  /**
   * @var int
   */
  public $numberFaces;
  protected $ocrAnnotationType = ImageSafesearchContentOCRAnnotation::class;
  protected $ocrAnnotationDataType = '';
  public $ocrAnnotation;
  /**
   * @var float
   */
  public $ocrVulgarScore;
  protected $offensiveSymbolDetectionType = ImageSafesearchContentOffensiveSymbolDetection::class;
  protected $offensiveSymbolDetectionDataType = '';
  public $offensiveSymbolDetection;
  /**
   * @var string
   */
  public $photodnaHash;
  /**
   * @var bool
   */
  public $pornWithHighConfidence;
  /**
   * @var float
   */
  public $qbstOffensiveScore;
  /**
   * @var float
   */
  public $qbstSpoofScore;
  protected $queryStatsType = ClassifierPornQueryStats::class;
  protected $queryStatsDataType = '';
  public $queryStats;
  /**
   * @var float
   */
  public $queryTextViolenceScore;
  /**
   * @var string
   */
  public $referer;
  protected $referrerCountsType = ClassifierPornReferrerCounts::class;
  protected $referrerCountsDataType = '';
  public $referrerCounts;
  /**
   * @var float
   */
  public $semanticSexualizationScore;
  /**
   * @var string
   */
  public $url;
  protected $urlPornScoresType = ClassifierPornAggregatedUrlPornScores::class;
  protected $urlPornScoresDataType = '';
  public $urlPornScores;

  /**
   * @param ImageSafesearchContentBrainPornAnnotation
   */
  public function setCoclickBrainScores(ImageSafesearchContentBrainPornAnnotation $coclickBrainScores)
  {
    $this->coclickBrainScores = $coclickBrainScores;
  }
  /**
   * @return ImageSafesearchContentBrainPornAnnotation
   */
  public function getCoclickBrainScores()
  {
    return $this->coclickBrainScores;
  }
  /**
   * @param float
   */
  public function setCsaiScore($csaiScore)
  {
    $this->csaiScore = $csaiScore;
  }
  /**
   * @return float
   */
  public function getCsaiScore()
  {
    return $this->csaiScore;
  }
  /**
   * @param ImagePornDebugInfo[]
   */
  public function setDebugInfo($debugInfo)
  {
    $this->debugInfo = $debugInfo;
  }
  /**
   * @return ImagePornDebugInfo[]
   */
  public function getDebugInfo()
  {
    return $this->debugInfo;
  }
  /**
   * @param float
   */
  public function setFinalOffensiveScore($finalOffensiveScore)
  {
    $this->finalOffensiveScore = $finalOffensiveScore;
  }
  /**
   * @return float
   */
  public function getFinalOffensiveScore()
  {
    return $this->finalOffensiveScore;
  }
  /**
   * @param float
   */
  public function setFinalViolenceScore($finalViolenceScore)
  {
    $this->finalViolenceScore = $finalViolenceScore;
  }
  /**
   * @return float
   */
  public function getFinalViolenceScore()
  {
    return $this->finalViolenceScore;
  }
  /**
   * @param string
   */
  public function setFinalViolenceScoreVersion($finalViolenceScoreVersion)
  {
    $this->finalViolenceScoreVersion = $finalViolenceScoreVersion;
  }
  /**
   * @return string
   */
  public function getFinalViolenceScoreVersion()
  {
    return $this->finalViolenceScoreVersion;
  }
  /**
   * @param SafesearchInternalImageSignals
   */
  public function setInternalSignals(SafesearchInternalImageSignals $internalSignals)
  {
    $this->internalSignals = $internalSignals;
  }
  /**
   * @return SafesearchInternalImageSignals
   */
  public function getInternalSignals()
  {
    return $this->internalSignals;
  }
  /**
   * @param int
   */
  public function setNumberFaces($numberFaces)
  {
    $this->numberFaces = $numberFaces;
  }
  /**
   * @return int
   */
  public function getNumberFaces()
  {
    return $this->numberFaces;
  }
  /**
   * @param ImageSafesearchContentOCRAnnotation
   */
  public function setOcrAnnotation(ImageSafesearchContentOCRAnnotation $ocrAnnotation)
  {
    $this->ocrAnnotation = $ocrAnnotation;
  }
  /**
   * @return ImageSafesearchContentOCRAnnotation
   */
  public function getOcrAnnotation()
  {
    return $this->ocrAnnotation;
  }
  /**
   * @param float
   */
  public function setOcrVulgarScore($ocrVulgarScore)
  {
    $this->ocrVulgarScore = $ocrVulgarScore;
  }
  /**
   * @return float
   */
  public function getOcrVulgarScore()
  {
    return $this->ocrVulgarScore;
  }
  /**
   * @param ImageSafesearchContentOffensiveSymbolDetection
   */
  public function setOffensiveSymbolDetection(ImageSafesearchContentOffensiveSymbolDetection $offensiveSymbolDetection)
  {
    $this->offensiveSymbolDetection = $offensiveSymbolDetection;
  }
  /**
   * @return ImageSafesearchContentOffensiveSymbolDetection
   */
  public function getOffensiveSymbolDetection()
  {
    return $this->offensiveSymbolDetection;
  }
  /**
   * @param string
   */
  public function setPhotodnaHash($photodnaHash)
  {
    $this->photodnaHash = $photodnaHash;
  }
  /**
   * @return string
   */
  public function getPhotodnaHash()
  {
    return $this->photodnaHash;
  }
  /**
   * @param bool
   */
  public function setPornWithHighConfidence($pornWithHighConfidence)
  {
    $this->pornWithHighConfidence = $pornWithHighConfidence;
  }
  /**
   * @return bool
   */
  public function getPornWithHighConfidence()
  {
    return $this->pornWithHighConfidence;
  }
  /**
   * @param float
   */
  public function setQbstOffensiveScore($qbstOffensiveScore)
  {
    $this->qbstOffensiveScore = $qbstOffensiveScore;
  }
  /**
   * @return float
   */
  public function getQbstOffensiveScore()
  {
    return $this->qbstOffensiveScore;
  }
  /**
   * @param float
   */
  public function setQbstSpoofScore($qbstSpoofScore)
  {
    $this->qbstSpoofScore = $qbstSpoofScore;
  }
  /**
   * @return float
   */
  public function getQbstSpoofScore()
  {
    return $this->qbstSpoofScore;
  }
  /**
   * @param ClassifierPornQueryStats
   */
  public function setQueryStats(ClassifierPornQueryStats $queryStats)
  {
    $this->queryStats = $queryStats;
  }
  /**
   * @return ClassifierPornQueryStats
   */
  public function getQueryStats()
  {
    return $this->queryStats;
  }
  /**
   * @param float
   */
  public function setQueryTextViolenceScore($queryTextViolenceScore)
  {
    $this->queryTextViolenceScore = $queryTextViolenceScore;
  }
  /**
   * @return float
   */
  public function getQueryTextViolenceScore()
  {
    return $this->queryTextViolenceScore;
  }
  /**
   * @param string
   */
  public function setReferer($referer)
  {
    $this->referer = $referer;
  }
  /**
   * @return string
   */
  public function getReferer()
  {
    return $this->referer;
  }
  /**
   * @param ClassifierPornReferrerCounts
   */
  public function setReferrerCounts(ClassifierPornReferrerCounts $referrerCounts)
  {
    $this->referrerCounts = $referrerCounts;
  }
  /**
   * @return ClassifierPornReferrerCounts
   */
  public function getReferrerCounts()
  {
    return $this->referrerCounts;
  }
  /**
   * @param float
   */
  public function setSemanticSexualizationScore($semanticSexualizationScore)
  {
    $this->semanticSexualizationScore = $semanticSexualizationScore;
  }
  /**
   * @return float
   */
  public function getSemanticSexualizationScore()
  {
    return $this->semanticSexualizationScore;
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
   * @param ClassifierPornAggregatedUrlPornScores
   */
  public function setUrlPornScores(ClassifierPornAggregatedUrlPornScores $urlPornScores)
  {
    $this->urlPornScores = $urlPornScores;
  }
  /**
   * @return ClassifierPornAggregatedUrlPornScores
   */
  public function getUrlPornScores()
  {
    return $this->urlPornScores;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PornFlagData::class, 'Google_Service_Contentwarehouse_PornFlagData');
