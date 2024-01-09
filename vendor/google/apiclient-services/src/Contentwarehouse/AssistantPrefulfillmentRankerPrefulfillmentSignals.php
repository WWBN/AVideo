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

class AssistantPrefulfillmentRankerPrefulfillmentSignals extends \Google\Model
{
  /**
   * @var float
   */
  public $bindingSetAuis;
  /**
   * @var float
   */
  public $bindingSetPauis;
  public $calibratedParsingScore;
  /**
   * @var bool
   */
  public $deepMediaDominant;
  /**
   * @var bool
   */
  public $dominant;
  /**
   * @var float
   */
  public $effectiveArgSpanLength;
  /**
   * @var bool
   */
  public $fulfillableDominantMedia;
  /**
   * @var bool
   */
  public $generatedByLegacyAquaDomain;
  public $groundabilityScore;
  protected $groundingProviderFeaturesType = AssistantGroundingRankerGroundingProviderFeatures::class;
  protected $groundingProviderFeaturesDataType = '';
  /**
   * @var bool
   */
  public $hasAnswerGroup;
  /**
   * @var float
   */
  public $inQueryMaxEffectiveArgSpanLength;
  /**
   * @var string
   */
  public $intentName;
  public $intentNameAuisScore;
  public $intentNameAuisScoreExp;
  /**
   * @var float
   */
  public $intentNamePauis;
  /**
   * @var bool
   */
  public $isFeasible;
  /**
   * @var bool
   */
  public $isFullyGrounded;
  /**
   * @var bool
   */
  public $isMediaControlIntent;
  /**
   * @var bool
   */
  public $isPlayGenericMusic;
  /**
   * @var bool
   */
  public $isPodcastIntent;
  /**
   * @var bool
   */
  public $isVideoIntent;
  /**
   * @var int
   */
  public $kscorerRank;
  protected $laaFeaturesType = AssistantGroundingRankerLaaFeatures::class;
  protected $laaFeaturesDataType = '';
  /**
   * @var bool
   */
  public $maskCandidateLevelFeatures;
  public $maxHgrScoreAcrossBindingSets;
  /**
   * @var int
   */
  public $nspRank;
  /**
   * @var float
   */
  public $numAlternativeHypothesis;
  public $numConstraints;
  public $numConstraintsSatisfied;
  public $numGroundableArgs;
  public $numGroundedArgs;
  public $numVariables;
  public $numVariablesGrounded;
  /**
   * @var int
   */
  public $parsingScoreMse8BucketId;
  /**
   * @var string
   */
  public $phase;
  public $pq2tVsAssistantIbstCosine;
  public $pq2tVsIbstCosine;
  /**
   * @var float
   */
  public $predictedIntentConfidence;
  /**
   * @var string
   */
  public $rankerName;
  /**
   * @var string
   */
  public $searchDispatch;
  /**
   * @var string
   */
  public $subIntentType;
  /**
   * @var float
   */
  public $topHypothesisConfidence;
  /**
   * @var float
   */
  public $verticalConfidenceScore;

  /**
   * @param float
   */
  public function setBindingSetAuis($bindingSetAuis)
  {
    $this->bindingSetAuis = $bindingSetAuis;
  }
  /**
   * @return float
   */
  public function getBindingSetAuis()
  {
    return $this->bindingSetAuis;
  }
  /**
   * @param float
   */
  public function setBindingSetPauis($bindingSetPauis)
  {
    $this->bindingSetPauis = $bindingSetPauis;
  }
  /**
   * @return float
   */
  public function getBindingSetPauis()
  {
    return $this->bindingSetPauis;
  }
  public function setCalibratedParsingScore($calibratedParsingScore)
  {
    $this->calibratedParsingScore = $calibratedParsingScore;
  }
  public function getCalibratedParsingScore()
  {
    return $this->calibratedParsingScore;
  }
  /**
   * @param bool
   */
  public function setDeepMediaDominant($deepMediaDominant)
  {
    $this->deepMediaDominant = $deepMediaDominant;
  }
  /**
   * @return bool
   */
  public function getDeepMediaDominant()
  {
    return $this->deepMediaDominant;
  }
  /**
   * @param bool
   */
  public function setDominant($dominant)
  {
    $this->dominant = $dominant;
  }
  /**
   * @return bool
   */
  public function getDominant()
  {
    return $this->dominant;
  }
  /**
   * @param float
   */
  public function setEffectiveArgSpanLength($effectiveArgSpanLength)
  {
    $this->effectiveArgSpanLength = $effectiveArgSpanLength;
  }
  /**
   * @return float
   */
  public function getEffectiveArgSpanLength()
  {
    return $this->effectiveArgSpanLength;
  }
  /**
   * @param bool
   */
  public function setFulfillableDominantMedia($fulfillableDominantMedia)
  {
    $this->fulfillableDominantMedia = $fulfillableDominantMedia;
  }
  /**
   * @return bool
   */
  public function getFulfillableDominantMedia()
  {
    return $this->fulfillableDominantMedia;
  }
  /**
   * @param bool
   */
  public function setGeneratedByLegacyAquaDomain($generatedByLegacyAquaDomain)
  {
    $this->generatedByLegacyAquaDomain = $generatedByLegacyAquaDomain;
  }
  /**
   * @return bool
   */
  public function getGeneratedByLegacyAquaDomain()
  {
    return $this->generatedByLegacyAquaDomain;
  }
  public function setGroundabilityScore($groundabilityScore)
  {
    $this->groundabilityScore = $groundabilityScore;
  }
  public function getGroundabilityScore()
  {
    return $this->groundabilityScore;
  }
  /**
   * @param AssistantGroundingRankerGroundingProviderFeatures
   */
  public function setGroundingProviderFeatures(AssistantGroundingRankerGroundingProviderFeatures $groundingProviderFeatures)
  {
    $this->groundingProviderFeatures = $groundingProviderFeatures;
  }
  /**
   * @return AssistantGroundingRankerGroundingProviderFeatures
   */
  public function getGroundingProviderFeatures()
  {
    return $this->groundingProviderFeatures;
  }
  /**
   * @param bool
   */
  public function setHasAnswerGroup($hasAnswerGroup)
  {
    $this->hasAnswerGroup = $hasAnswerGroup;
  }
  /**
   * @return bool
   */
  public function getHasAnswerGroup()
  {
    return $this->hasAnswerGroup;
  }
  /**
   * @param float
   */
  public function setInQueryMaxEffectiveArgSpanLength($inQueryMaxEffectiveArgSpanLength)
  {
    $this->inQueryMaxEffectiveArgSpanLength = $inQueryMaxEffectiveArgSpanLength;
  }
  /**
   * @return float
   */
  public function getInQueryMaxEffectiveArgSpanLength()
  {
    return $this->inQueryMaxEffectiveArgSpanLength;
  }
  /**
   * @param string
   */
  public function setIntentName($intentName)
  {
    $this->intentName = $intentName;
  }
  /**
   * @return string
   */
  public function getIntentName()
  {
    return $this->intentName;
  }
  public function setIntentNameAuisScore($intentNameAuisScore)
  {
    $this->intentNameAuisScore = $intentNameAuisScore;
  }
  public function getIntentNameAuisScore()
  {
    return $this->intentNameAuisScore;
  }
  public function setIntentNameAuisScoreExp($intentNameAuisScoreExp)
  {
    $this->intentNameAuisScoreExp = $intentNameAuisScoreExp;
  }
  public function getIntentNameAuisScoreExp()
  {
    return $this->intentNameAuisScoreExp;
  }
  /**
   * @param float
   */
  public function setIntentNamePauis($intentNamePauis)
  {
    $this->intentNamePauis = $intentNamePauis;
  }
  /**
   * @return float
   */
  public function getIntentNamePauis()
  {
    return $this->intentNamePauis;
  }
  /**
   * @param bool
   */
  public function setIsFeasible($isFeasible)
  {
    $this->isFeasible = $isFeasible;
  }
  /**
   * @return bool
   */
  public function getIsFeasible()
  {
    return $this->isFeasible;
  }
  /**
   * @param bool
   */
  public function setIsFullyGrounded($isFullyGrounded)
  {
    $this->isFullyGrounded = $isFullyGrounded;
  }
  /**
   * @return bool
   */
  public function getIsFullyGrounded()
  {
    return $this->isFullyGrounded;
  }
  /**
   * @param bool
   */
  public function setIsMediaControlIntent($isMediaControlIntent)
  {
    $this->isMediaControlIntent = $isMediaControlIntent;
  }
  /**
   * @return bool
   */
  public function getIsMediaControlIntent()
  {
    return $this->isMediaControlIntent;
  }
  /**
   * @param bool
   */
  public function setIsPlayGenericMusic($isPlayGenericMusic)
  {
    $this->isPlayGenericMusic = $isPlayGenericMusic;
  }
  /**
   * @return bool
   */
  public function getIsPlayGenericMusic()
  {
    return $this->isPlayGenericMusic;
  }
  /**
   * @param bool
   */
  public function setIsPodcastIntent($isPodcastIntent)
  {
    $this->isPodcastIntent = $isPodcastIntent;
  }
  /**
   * @return bool
   */
  public function getIsPodcastIntent()
  {
    return $this->isPodcastIntent;
  }
  /**
   * @param bool
   */
  public function setIsVideoIntent($isVideoIntent)
  {
    $this->isVideoIntent = $isVideoIntent;
  }
  /**
   * @return bool
   */
  public function getIsVideoIntent()
  {
    return $this->isVideoIntent;
  }
  /**
   * @param int
   */
  public function setKscorerRank($kscorerRank)
  {
    $this->kscorerRank = $kscorerRank;
  }
  /**
   * @return int
   */
  public function getKscorerRank()
  {
    return $this->kscorerRank;
  }
  /**
   * @param AssistantGroundingRankerLaaFeatures
   */
  public function setLaaFeatures(AssistantGroundingRankerLaaFeatures $laaFeatures)
  {
    $this->laaFeatures = $laaFeatures;
  }
  /**
   * @return AssistantGroundingRankerLaaFeatures
   */
  public function getLaaFeatures()
  {
    return $this->laaFeatures;
  }
  /**
   * @param bool
   */
  public function setMaskCandidateLevelFeatures($maskCandidateLevelFeatures)
  {
    $this->maskCandidateLevelFeatures = $maskCandidateLevelFeatures;
  }
  /**
   * @return bool
   */
  public function getMaskCandidateLevelFeatures()
  {
    return $this->maskCandidateLevelFeatures;
  }
  public function setMaxHgrScoreAcrossBindingSets($maxHgrScoreAcrossBindingSets)
  {
    $this->maxHgrScoreAcrossBindingSets = $maxHgrScoreAcrossBindingSets;
  }
  public function getMaxHgrScoreAcrossBindingSets()
  {
    return $this->maxHgrScoreAcrossBindingSets;
  }
  /**
   * @param int
   */
  public function setNspRank($nspRank)
  {
    $this->nspRank = $nspRank;
  }
  /**
   * @return int
   */
  public function getNspRank()
  {
    return $this->nspRank;
  }
  /**
   * @param float
   */
  public function setNumAlternativeHypothesis($numAlternativeHypothesis)
  {
    $this->numAlternativeHypothesis = $numAlternativeHypothesis;
  }
  /**
   * @return float
   */
  public function getNumAlternativeHypothesis()
  {
    return $this->numAlternativeHypothesis;
  }
  public function setNumConstraints($numConstraints)
  {
    $this->numConstraints = $numConstraints;
  }
  public function getNumConstraints()
  {
    return $this->numConstraints;
  }
  public function setNumConstraintsSatisfied($numConstraintsSatisfied)
  {
    $this->numConstraintsSatisfied = $numConstraintsSatisfied;
  }
  public function getNumConstraintsSatisfied()
  {
    return $this->numConstraintsSatisfied;
  }
  public function setNumGroundableArgs($numGroundableArgs)
  {
    $this->numGroundableArgs = $numGroundableArgs;
  }
  public function getNumGroundableArgs()
  {
    return $this->numGroundableArgs;
  }
  public function setNumGroundedArgs($numGroundedArgs)
  {
    $this->numGroundedArgs = $numGroundedArgs;
  }
  public function getNumGroundedArgs()
  {
    return $this->numGroundedArgs;
  }
  public function setNumVariables($numVariables)
  {
    $this->numVariables = $numVariables;
  }
  public function getNumVariables()
  {
    return $this->numVariables;
  }
  public function setNumVariablesGrounded($numVariablesGrounded)
  {
    $this->numVariablesGrounded = $numVariablesGrounded;
  }
  public function getNumVariablesGrounded()
  {
    return $this->numVariablesGrounded;
  }
  /**
   * @param int
   */
  public function setParsingScoreMse8BucketId($parsingScoreMse8BucketId)
  {
    $this->parsingScoreMse8BucketId = $parsingScoreMse8BucketId;
  }
  /**
   * @return int
   */
  public function getParsingScoreMse8BucketId()
  {
    return $this->parsingScoreMse8BucketId;
  }
  /**
   * @param string
   */
  public function setPhase($phase)
  {
    $this->phase = $phase;
  }
  /**
   * @return string
   */
  public function getPhase()
  {
    return $this->phase;
  }
  public function setPq2tVsAssistantIbstCosine($pq2tVsAssistantIbstCosine)
  {
    $this->pq2tVsAssistantIbstCosine = $pq2tVsAssistantIbstCosine;
  }
  public function getPq2tVsAssistantIbstCosine()
  {
    return $this->pq2tVsAssistantIbstCosine;
  }
  public function setPq2tVsIbstCosine($pq2tVsIbstCosine)
  {
    $this->pq2tVsIbstCosine = $pq2tVsIbstCosine;
  }
  public function getPq2tVsIbstCosine()
  {
    return $this->pq2tVsIbstCosine;
  }
  /**
   * @param float
   */
  public function setPredictedIntentConfidence($predictedIntentConfidence)
  {
    $this->predictedIntentConfidence = $predictedIntentConfidence;
  }
  /**
   * @return float
   */
  public function getPredictedIntentConfidence()
  {
    return $this->predictedIntentConfidence;
  }
  /**
   * @param string
   */
  public function setRankerName($rankerName)
  {
    $this->rankerName = $rankerName;
  }
  /**
   * @return string
   */
  public function getRankerName()
  {
    return $this->rankerName;
  }
  /**
   * @param string
   */
  public function setSearchDispatch($searchDispatch)
  {
    $this->searchDispatch = $searchDispatch;
  }
  /**
   * @return string
   */
  public function getSearchDispatch()
  {
    return $this->searchDispatch;
  }
  /**
   * @param string
   */
  public function setSubIntentType($subIntentType)
  {
    $this->subIntentType = $subIntentType;
  }
  /**
   * @return string
   */
  public function getSubIntentType()
  {
    return $this->subIntentType;
  }
  /**
   * @param float
   */
  public function setTopHypothesisConfidence($topHypothesisConfidence)
  {
    $this->topHypothesisConfidence = $topHypothesisConfidence;
  }
  /**
   * @return float
   */
  public function getTopHypothesisConfidence()
  {
    return $this->topHypothesisConfidence;
  }
  /**
   * @param float
   */
  public function setVerticalConfidenceScore($verticalConfidenceScore)
  {
    $this->verticalConfidenceScore = $verticalConfidenceScore;
  }
  /**
   * @return float
   */
  public function getVerticalConfidenceScore()
  {
    return $this->verticalConfidenceScore;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantPrefulfillmentRankerPrefulfillmentSignals::class, 'Google_Service_Contentwarehouse_AssistantPrefulfillmentRankerPrefulfillmentSignals');
