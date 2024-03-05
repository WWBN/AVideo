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

class LearningGenaiRootTakedownResult extends \Google\Model
{
  /**
   * @var bool
   */
  public $allowed;
  protected $regexTakedownResultType = LearningGenaiRootRegexTakedownResult::class;
  protected $regexTakedownResultDataType = '';
  protected $requestResponseTakedownResultType = LearningGenaiRootRequestResponseTakedownResult::class;
  protected $requestResponseTakedownResultDataType = '';
  protected $similarityTakedownResultType = LearningGenaiRootSimilarityTakedownResult::class;
  protected $similarityTakedownResultDataType = '';

  /**
   * @param bool
   */
  public function setAllowed($allowed)
  {
    $this->allowed = $allowed;
  }
  /**
   * @return bool
   */
  public function getAllowed()
  {
    return $this->allowed;
  }
  /**
   * @param LearningGenaiRootRegexTakedownResult
   */
  public function setRegexTakedownResult(LearningGenaiRootRegexTakedownResult $regexTakedownResult)
  {
    $this->regexTakedownResult = $regexTakedownResult;
  }
  /**
   * @return LearningGenaiRootRegexTakedownResult
   */
  public function getRegexTakedownResult()
  {
    return $this->regexTakedownResult;
  }
  /**
   * @param LearningGenaiRootRequestResponseTakedownResult
   */
  public function setRequestResponseTakedownResult(LearningGenaiRootRequestResponseTakedownResult $requestResponseTakedownResult)
  {
    $this->requestResponseTakedownResult = $requestResponseTakedownResult;
  }
  /**
   * @return LearningGenaiRootRequestResponseTakedownResult
   */
  public function getRequestResponseTakedownResult()
  {
    return $this->requestResponseTakedownResult;
  }
  /**
   * @param LearningGenaiRootSimilarityTakedownResult
   */
  public function setSimilarityTakedownResult(LearningGenaiRootSimilarityTakedownResult $similarityTakedownResult)
  {
    $this->similarityTakedownResult = $similarityTakedownResult;
  }
  /**
   * @return LearningGenaiRootSimilarityTakedownResult
   */
  public function getSimilarityTakedownResult()
  {
    return $this->similarityTakedownResult;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootTakedownResult::class, 'Google_Service_Aiplatform_LearningGenaiRootTakedownResult');
