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

class CloudAiNlLlmProtoServiceRaiResult extends \Google\Collection
{
  protected $collection_key = 'raiSignals';
  protected $aidaRecitationResultType = LanguageLabsAidaTrustRecitationProtoRecitationResult::class;
  protected $aidaRecitationResultDataType = '';
  /**
   * @var bool
   */
  public $blocked;
  /**
   * @var int[]
   */
  public $errorCodes;
  /**
   * @var bool
   */
  public $filtered;
  protected $languageFilterResultType = LearningGenaiRootLanguageFilterResult::class;
  protected $languageFilterResultDataType = '';
  protected $raiSignalsType = CloudAiNlLlmProtoServiceRaiSignal::class;
  protected $raiSignalsDataType = 'array';
  /**
   * @var bool
   */
  public $triggeredBlocklist;
  /**
   * @var bool
   */
  public $triggeredRecitation;
  /**
   * @var bool
   */
  public $triggeredSafetyFilter;

  /**
   * @param LanguageLabsAidaTrustRecitationProtoRecitationResult
   */
  public function setAidaRecitationResult(LanguageLabsAidaTrustRecitationProtoRecitationResult $aidaRecitationResult)
  {
    $this->aidaRecitationResult = $aidaRecitationResult;
  }
  /**
   * @return LanguageLabsAidaTrustRecitationProtoRecitationResult
   */
  public function getAidaRecitationResult()
  {
    return $this->aidaRecitationResult;
  }
  /**
   * @param bool
   */
  public function setBlocked($blocked)
  {
    $this->blocked = $blocked;
  }
  /**
   * @return bool
   */
  public function getBlocked()
  {
    return $this->blocked;
  }
  /**
   * @param int[]
   */
  public function setErrorCodes($errorCodes)
  {
    $this->errorCodes = $errorCodes;
  }
  /**
   * @return int[]
   */
  public function getErrorCodes()
  {
    return $this->errorCodes;
  }
  /**
   * @param bool
   */
  public function setFiltered($filtered)
  {
    $this->filtered = $filtered;
  }
  /**
   * @return bool
   */
  public function getFiltered()
  {
    return $this->filtered;
  }
  /**
   * @param LearningGenaiRootLanguageFilterResult
   */
  public function setLanguageFilterResult(LearningGenaiRootLanguageFilterResult $languageFilterResult)
  {
    $this->languageFilterResult = $languageFilterResult;
  }
  /**
   * @return LearningGenaiRootLanguageFilterResult
   */
  public function getLanguageFilterResult()
  {
    return $this->languageFilterResult;
  }
  /**
   * @param CloudAiNlLlmProtoServiceRaiSignal[]
   */
  public function setRaiSignals($raiSignals)
  {
    $this->raiSignals = $raiSignals;
  }
  /**
   * @return CloudAiNlLlmProtoServiceRaiSignal[]
   */
  public function getRaiSignals()
  {
    return $this->raiSignals;
  }
  /**
   * @param bool
   */
  public function setTriggeredBlocklist($triggeredBlocklist)
  {
    $this->triggeredBlocklist = $triggeredBlocklist;
  }
  /**
   * @return bool
   */
  public function getTriggeredBlocklist()
  {
    return $this->triggeredBlocklist;
  }
  /**
   * @param bool
   */
  public function setTriggeredRecitation($triggeredRecitation)
  {
    $this->triggeredRecitation = $triggeredRecitation;
  }
  /**
   * @return bool
   */
  public function getTriggeredRecitation()
  {
    return $this->triggeredRecitation;
  }
  /**
   * @param bool
   */
  public function setTriggeredSafetyFilter($triggeredSafetyFilter)
  {
    $this->triggeredSafetyFilter = $triggeredSafetyFilter;
  }
  /**
   * @return bool
   */
  public function getTriggeredSafetyFilter()
  {
    return $this->triggeredSafetyFilter;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CloudAiNlLlmProtoServiceRaiResult::class, 'Google_Service_Aiplatform_CloudAiNlLlmProtoServiceRaiResult');
