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

namespace Google\Service\Aiplatform;

class LearningServingLlmMessageMetadata extends \Google\Collection
{
  protected $collection_key = 'translatedUserPrompts';
  protected $classifierSummaryType = LearningGenaiRootClassifierOutputSummary::class;
  protected $classifierSummaryDataType = '';
  protected $codeyOutputType = LearningGenaiRootCodeyOutput::class;
  protected $codeyOutputDataType = '';
  /**
   * @var string
   */
  public $currentStreamTextLength;
  /**
   * @var bool
   */
  public $deleted;
  protected $filterMetaType = LearningGenaiRootFilterMetadata::class;
  protected $filterMetaDataType = 'array';
  protected $finalMessageScoreType = LearningGenaiRootScore::class;
  protected $finalMessageScoreDataType = '';
  /**
   * @var string
   */
  public $finishReason;
  protected $groundingMetadataType = LearningGenaiRootGroundingMetadata::class;
  protected $groundingMetadataDataType = '';
  /**
   * @var bool
   */
  public $isCode;
  /**
   * @var bool
   */
  public $isFallback;
  protected $langidResultType = NlpSaftLangIdResult::class;
  protected $langidResultDataType = '';
  /**
   * @var string
   */
  public $language;
  /**
   * @var string
   */
  public $lmPrefix;
  /**
   * @var string
   */
  public $originalText;
  /**
   * @var int
   */
  public $perStreamDecodedTokenCount;
  protected $raiOutputsType = LearningGenaiRootRAIOutput::class;
  protected $raiOutputsDataType = 'array';
  protected $recitationResultType = LearningGenaiRecitationRecitationResult::class;
  protected $recitationResultDataType = '';
  /**
   * @var int
   */
  public $returnTokenCount;
  protected $scoresType = LearningGenaiRootScore::class;
  protected $scoresDataType = 'array';
  /**
   * @var bool
   */
  public $streamTerminated;
  /**
   * @var int
   */
  public $totalDecodedTokenCount;
  /**
   * @var string[]
   */
  public $translatedUserPrompts;
  protected $vertexRaiResultType = CloudAiNlLlmProtoServiceRaiResult::class;
  protected $vertexRaiResultDataType = '';

  /**
   * @param LearningGenaiRootClassifierOutputSummary
   */
  public function setClassifierSummary(LearningGenaiRootClassifierOutputSummary $classifierSummary)
  {
    $this->classifierSummary = $classifierSummary;
  }
  /**
   * @return LearningGenaiRootClassifierOutputSummary
   */
  public function getClassifierSummary()
  {
    return $this->classifierSummary;
  }
  /**
   * @param LearningGenaiRootCodeyOutput
   */
  public function setCodeyOutput(LearningGenaiRootCodeyOutput $codeyOutput)
  {
    $this->codeyOutput = $codeyOutput;
  }
  /**
   * @return LearningGenaiRootCodeyOutput
   */
  public function getCodeyOutput()
  {
    return $this->codeyOutput;
  }
  /**
   * @param string
   */
  public function setCurrentStreamTextLength($currentStreamTextLength)
  {
    $this->currentStreamTextLength = $currentStreamTextLength;
  }
  /**
   * @return string
   */
  public function getCurrentStreamTextLength()
  {
    return $this->currentStreamTextLength;
  }
  /**
   * @param bool
   */
  public function setDeleted($deleted)
  {
    $this->deleted = $deleted;
  }
  /**
   * @return bool
   */
  public function getDeleted()
  {
    return $this->deleted;
  }
  /**
   * @param LearningGenaiRootFilterMetadata[]
   */
  public function setFilterMeta($filterMeta)
  {
    $this->filterMeta = $filterMeta;
  }
  /**
   * @return LearningGenaiRootFilterMetadata[]
   */
  public function getFilterMeta()
  {
    return $this->filterMeta;
  }
  /**
   * @param LearningGenaiRootScore
   */
  public function setFinalMessageScore(LearningGenaiRootScore $finalMessageScore)
  {
    $this->finalMessageScore = $finalMessageScore;
  }
  /**
   * @return LearningGenaiRootScore
   */
  public function getFinalMessageScore()
  {
    return $this->finalMessageScore;
  }
  /**
   * @param string
   */
  public function setFinishReason($finishReason)
  {
    $this->finishReason = $finishReason;
  }
  /**
   * @return string
   */
  public function getFinishReason()
  {
    return $this->finishReason;
  }
  /**
   * @param LearningGenaiRootGroundingMetadata
   */
  public function setGroundingMetadata(LearningGenaiRootGroundingMetadata $groundingMetadata)
  {
    $this->groundingMetadata = $groundingMetadata;
  }
  /**
   * @return LearningGenaiRootGroundingMetadata
   */
  public function getGroundingMetadata()
  {
    return $this->groundingMetadata;
  }
  /**
   * @param bool
   */
  public function setIsCode($isCode)
  {
    $this->isCode = $isCode;
  }
  /**
   * @return bool
   */
  public function getIsCode()
  {
    return $this->isCode;
  }
  /**
   * @param bool
   */
  public function setIsFallback($isFallback)
  {
    $this->isFallback = $isFallback;
  }
  /**
   * @return bool
   */
  public function getIsFallback()
  {
    return $this->isFallback;
  }
  /**
   * @param NlpSaftLangIdResult
   */
  public function setLangidResult(NlpSaftLangIdResult $langidResult)
  {
    $this->langidResult = $langidResult;
  }
  /**
   * @return NlpSaftLangIdResult
   */
  public function getLangidResult()
  {
    return $this->langidResult;
  }
  /**
   * @param string
   */
  public function setLanguage($language)
  {
    $this->language = $language;
  }
  /**
   * @return string
   */
  public function getLanguage()
  {
    return $this->language;
  }
  /**
   * @param string
   */
  public function setLmPrefix($lmPrefix)
  {
    $this->lmPrefix = $lmPrefix;
  }
  /**
   * @return string
   */
  public function getLmPrefix()
  {
    return $this->lmPrefix;
  }
  /**
   * @param string
   */
  public function setOriginalText($originalText)
  {
    $this->originalText = $originalText;
  }
  /**
   * @return string
   */
  public function getOriginalText()
  {
    return $this->originalText;
  }
  /**
   * @param int
   */
  public function setPerStreamDecodedTokenCount($perStreamDecodedTokenCount)
  {
    $this->perStreamDecodedTokenCount = $perStreamDecodedTokenCount;
  }
  /**
   * @return int
   */
  public function getPerStreamDecodedTokenCount()
  {
    return $this->perStreamDecodedTokenCount;
  }
  /**
   * @param LearningGenaiRootRAIOutput[]
   */
  public function setRaiOutputs($raiOutputs)
  {
    $this->raiOutputs = $raiOutputs;
  }
  /**
   * @return LearningGenaiRootRAIOutput[]
   */
  public function getRaiOutputs()
  {
    return $this->raiOutputs;
  }
  /**
   * @param LearningGenaiRecitationRecitationResult
   */
  public function setRecitationResult(LearningGenaiRecitationRecitationResult $recitationResult)
  {
    $this->recitationResult = $recitationResult;
  }
  /**
   * @return LearningGenaiRecitationRecitationResult
   */
  public function getRecitationResult()
  {
    return $this->recitationResult;
  }
  /**
   * @param int
   */
  public function setReturnTokenCount($returnTokenCount)
  {
    $this->returnTokenCount = $returnTokenCount;
  }
  /**
   * @return int
   */
  public function getReturnTokenCount()
  {
    return $this->returnTokenCount;
  }
  /**
   * @param LearningGenaiRootScore[]
   */
  public function setScores($scores)
  {
    $this->scores = $scores;
  }
  /**
   * @return LearningGenaiRootScore[]
   */
  public function getScores()
  {
    return $this->scores;
  }
  /**
   * @param bool
   */
  public function setStreamTerminated($streamTerminated)
  {
    $this->streamTerminated = $streamTerminated;
  }
  /**
   * @return bool
   */
  public function getStreamTerminated()
  {
    return $this->streamTerminated;
  }
  /**
   * @param int
   */
  public function setTotalDecodedTokenCount($totalDecodedTokenCount)
  {
    $this->totalDecodedTokenCount = $totalDecodedTokenCount;
  }
  /**
   * @return int
   */
  public function getTotalDecodedTokenCount()
  {
    return $this->totalDecodedTokenCount;
  }
  /**
   * @param string[]
   */
  public function setTranslatedUserPrompts($translatedUserPrompts)
  {
    $this->translatedUserPrompts = $translatedUserPrompts;
  }
  /**
   * @return string[]
   */
  public function getTranslatedUserPrompts()
  {
    return $this->translatedUserPrompts;
  }
  /**
   * @param CloudAiNlLlmProtoServiceRaiResult
   */
  public function setVertexRaiResult(CloudAiNlLlmProtoServiceRaiResult $vertexRaiResult)
  {
    $this->vertexRaiResult = $vertexRaiResult;
  }
  /**
   * @return CloudAiNlLlmProtoServiceRaiResult
   */
  public function getVertexRaiResult()
  {
    return $this->vertexRaiResult;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningServingLlmMessageMetadata::class, 'Google_Service_Aiplatform_LearningServingLlmMessageMetadata');
