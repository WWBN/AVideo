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

class LearningGenaiRootFilterMetadataFilterDebugInfo extends \Google\Model
{
  protected $classifierOutputType = LearningGenaiRootClassifierOutput::class;
  protected $classifierOutputDataType = '';
  /**
   * @var string
   */
  public $defaultMetadata;
  protected $languageFilterResultType = LearningGenaiRootLanguageFilterResult::class;
  protected $languageFilterResultDataType = '';
  protected $raiOutputType = LearningGenaiRootRAIOutput::class;
  protected $raiOutputDataType = '';
  protected $raiResultType = CloudAiNlLlmProtoServiceRaiResult::class;
  protected $raiResultDataType = '';
  protected $raiSignalType = CloudAiNlLlmProtoServiceRaiSignal::class;
  protected $raiSignalDataType = '';
  protected $streamRecitationResultType = LanguageLabsAidaTrustRecitationProtoStreamRecitationResult::class;
  protected $streamRecitationResultDataType = '';
  protected $takedownResultType = LearningGenaiRootTakedownResult::class;
  protected $takedownResultDataType = '';
  protected $toxicityResultType = LearningGenaiRootToxicityResult::class;
  protected $toxicityResultDataType = '';

  /**
   * @param LearningGenaiRootClassifierOutput
   */
  public function setClassifierOutput(LearningGenaiRootClassifierOutput $classifierOutput)
  {
    $this->classifierOutput = $classifierOutput;
  }
  /**
   * @return LearningGenaiRootClassifierOutput
   */
  public function getClassifierOutput()
  {
    return $this->classifierOutput;
  }
  /**
   * @param string
   */
  public function setDefaultMetadata($defaultMetadata)
  {
    $this->defaultMetadata = $defaultMetadata;
  }
  /**
   * @return string
   */
  public function getDefaultMetadata()
  {
    return $this->defaultMetadata;
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
   * @param LearningGenaiRootRAIOutput
   */
  public function setRaiOutput(LearningGenaiRootRAIOutput $raiOutput)
  {
    $this->raiOutput = $raiOutput;
  }
  /**
   * @return LearningGenaiRootRAIOutput
   */
  public function getRaiOutput()
  {
    return $this->raiOutput;
  }
  /**
   * @param CloudAiNlLlmProtoServiceRaiResult
   */
  public function setRaiResult(CloudAiNlLlmProtoServiceRaiResult $raiResult)
  {
    $this->raiResult = $raiResult;
  }
  /**
   * @return CloudAiNlLlmProtoServiceRaiResult
   */
  public function getRaiResult()
  {
    return $this->raiResult;
  }
  /**
   * @param CloudAiNlLlmProtoServiceRaiSignal
   */
  public function setRaiSignal(CloudAiNlLlmProtoServiceRaiSignal $raiSignal)
  {
    $this->raiSignal = $raiSignal;
  }
  /**
   * @return CloudAiNlLlmProtoServiceRaiSignal
   */
  public function getRaiSignal()
  {
    return $this->raiSignal;
  }
  /**
   * @param LanguageLabsAidaTrustRecitationProtoStreamRecitationResult
   */
  public function setStreamRecitationResult(LanguageLabsAidaTrustRecitationProtoStreamRecitationResult $streamRecitationResult)
  {
    $this->streamRecitationResult = $streamRecitationResult;
  }
  /**
   * @return LanguageLabsAidaTrustRecitationProtoStreamRecitationResult
   */
  public function getStreamRecitationResult()
  {
    return $this->streamRecitationResult;
  }
  /**
   * @param LearningGenaiRootTakedownResult
   */
  public function setTakedownResult(LearningGenaiRootTakedownResult $takedownResult)
  {
    $this->takedownResult = $takedownResult;
  }
  /**
   * @return LearningGenaiRootTakedownResult
   */
  public function getTakedownResult()
  {
    return $this->takedownResult;
  }
  /**
   * @param LearningGenaiRootToxicityResult
   */
  public function setToxicityResult(LearningGenaiRootToxicityResult $toxicityResult)
  {
    $this->toxicityResult = $toxicityResult;
  }
  /**
   * @return LearningGenaiRootToxicityResult
   */
  public function getToxicityResult()
  {
    return $this->toxicityResult;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootFilterMetadataFilterDebugInfo::class, 'Google_Service_Aiplatform_LearningGenaiRootFilterMetadataFilterDebugInfo');
