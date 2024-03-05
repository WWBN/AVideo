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

class LearningGenaiRootScore extends \Google\Model
{
  protected $calculationTypeType = LearningGenaiRootCalculationType::class;
  protected $calculationTypeDataType = '';
  protected $internalMetadataType = LearningGenaiRootInternalMetadata::class;
  protected $internalMetadataDataType = '';
  protected $thresholdTypeType = LearningGenaiRootThresholdType::class;
  protected $thresholdTypeDataType = '';
  protected $tokensAndLogprobPerDecodingStepType = LearningGenaiRootTokensAndLogProbPerDecodingStep::class;
  protected $tokensAndLogprobPerDecodingStepDataType = '';
  public $value;

  /**
   * @param LearningGenaiRootCalculationType
   */
  public function setCalculationType(LearningGenaiRootCalculationType $calculationType)
  {
    $this->calculationType = $calculationType;
  }
  /**
   * @return LearningGenaiRootCalculationType
   */
  public function getCalculationType()
  {
    return $this->calculationType;
  }
  /**
   * @param LearningGenaiRootInternalMetadata
   */
  public function setInternalMetadata(LearningGenaiRootInternalMetadata $internalMetadata)
  {
    $this->internalMetadata = $internalMetadata;
  }
  /**
   * @return LearningGenaiRootInternalMetadata
   */
  public function getInternalMetadata()
  {
    return $this->internalMetadata;
  }
  /**
   * @param LearningGenaiRootThresholdType
   */
  public function setThresholdType(LearningGenaiRootThresholdType $thresholdType)
  {
    $this->thresholdType = $thresholdType;
  }
  /**
   * @return LearningGenaiRootThresholdType
   */
  public function getThresholdType()
  {
    return $this->thresholdType;
  }
  /**
   * @param LearningGenaiRootTokensAndLogProbPerDecodingStep
   */
  public function setTokensAndLogprobPerDecodingStep(LearningGenaiRootTokensAndLogProbPerDecodingStep $tokensAndLogprobPerDecodingStep)
  {
    $this->tokensAndLogprobPerDecodingStep = $tokensAndLogprobPerDecodingStep;
  }
  /**
   * @return LearningGenaiRootTokensAndLogProbPerDecodingStep
   */
  public function getTokensAndLogprobPerDecodingStep()
  {
    return $this->tokensAndLogprobPerDecodingStep;
  }
  public function setValue($value)
  {
    $this->value = $value;
  }
  public function getValue()
  {
    return $this->value;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootScore::class, 'Google_Service_Aiplatform_LearningGenaiRootScore');
