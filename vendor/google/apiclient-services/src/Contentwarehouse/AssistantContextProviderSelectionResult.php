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

class AssistantContextProviderSelectionResult extends \Google\Collection
{
  protected $collection_key = 'policyApplied';
  /**
   * @var string
   */
  public $bucketedFinalScore;
  /**
   * @var float
   */
  public $finalScore;
  protected $policyAppliedType = AssistantContextProviderSelectionPolicy::class;
  protected $policyAppliedDataType = 'array';
  /**
   * @var bool
   */
  public $shouldPrune;

  /**
   * @param string
   */
  public function setBucketedFinalScore($bucketedFinalScore)
  {
    $this->bucketedFinalScore = $bucketedFinalScore;
  }
  /**
   * @return string
   */
  public function getBucketedFinalScore()
  {
    return $this->bucketedFinalScore;
  }
  /**
   * @param float
   */
  public function setFinalScore($finalScore)
  {
    $this->finalScore = $finalScore;
  }
  /**
   * @return float
   */
  public function getFinalScore()
  {
    return $this->finalScore;
  }
  /**
   * @param AssistantContextProviderSelectionPolicy[]
   */
  public function setPolicyApplied($policyApplied)
  {
    $this->policyApplied = $policyApplied;
  }
  /**
   * @return AssistantContextProviderSelectionPolicy[]
   */
  public function getPolicyApplied()
  {
    return $this->policyApplied;
  }
  /**
   * @param bool
   */
  public function setShouldPrune($shouldPrune)
  {
    $this->shouldPrune = $shouldPrune;
  }
  /**
   * @return bool
   */
  public function getShouldPrune()
  {
    return $this->shouldPrune;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantContextProviderSelectionResult::class, 'Google_Service_Contentwarehouse_AssistantContextProviderSelectionResult');
