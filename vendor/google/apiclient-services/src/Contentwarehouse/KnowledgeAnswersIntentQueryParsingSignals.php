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

class KnowledgeAnswersIntentQueryParsingSignals extends \Google\Model
{
  public $calibratedParsingScore;
  /**
   * @var float
   */
  public $effectiveArgSpanLength;
  /**
   * @var float
   */
  public $inQueryMaxEffectiveArgSpanLength;
  protected $qrewriteCallPathInfoType = NlpLoggingQRewriteClientCallPathInfo::class;
  protected $qrewriteCallPathInfoDataType = '';
  public $qrewriteCallPathInfo;
  /**
   * @var string
   */
  public $qrewriteCallPathInfoFingerprint;
  /**
   * @var string
   */
  public $source;

  public function setCalibratedParsingScore($calibratedParsingScore)
  {
    $this->calibratedParsingScore = $calibratedParsingScore;
  }
  public function getCalibratedParsingScore()
  {
    return $this->calibratedParsingScore;
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
   * @param NlpLoggingQRewriteClientCallPathInfo
   */
  public function setQrewriteCallPathInfo(NlpLoggingQRewriteClientCallPathInfo $qrewriteCallPathInfo)
  {
    $this->qrewriteCallPathInfo = $qrewriteCallPathInfo;
  }
  /**
   * @return NlpLoggingQRewriteClientCallPathInfo
   */
  public function getQrewriteCallPathInfo()
  {
    return $this->qrewriteCallPathInfo;
  }
  /**
   * @param string
   */
  public function setQrewriteCallPathInfoFingerprint($qrewriteCallPathInfoFingerprint)
  {
    $this->qrewriteCallPathInfoFingerprint = $qrewriteCallPathInfoFingerprint;
  }
  /**
   * @return string
   */
  public function getQrewriteCallPathInfoFingerprint()
  {
    return $this->qrewriteCallPathInfoFingerprint;
  }
  /**
   * @param string
   */
  public function setSource($source)
  {
    $this->source = $source;
  }
  /**
   * @return string
   */
  public function getSource()
  {
    return $this->source;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(KnowledgeAnswersIntentQueryParsingSignals::class, 'Google_Service_Contentwarehouse_KnowledgeAnswersIntentQueryParsingSignals');
