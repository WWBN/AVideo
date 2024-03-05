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

class LearningGenaiRootScoredSimilarityTakedownPhrase extends \Google\Model
{
  protected $phraseType = LearningGenaiRootSimilarityTakedownPhrase::class;
  protected $phraseDataType = '';
  /**
   * @var float
   */
  public $similarityScore;

  /**
   * @param LearningGenaiRootSimilarityTakedownPhrase
   */
  public function setPhrase(LearningGenaiRootSimilarityTakedownPhrase $phrase)
  {
    $this->phrase = $phrase;
  }
  /**
   * @return LearningGenaiRootSimilarityTakedownPhrase
   */
  public function getPhrase()
  {
    return $this->phrase;
  }
  /**
   * @param float
   */
  public function setSimilarityScore($similarityScore)
  {
    $this->similarityScore = $similarityScore;
  }
  /**
   * @return float
   */
  public function getSimilarityScore()
  {
    return $this->similarityScore;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootScoredSimilarityTakedownPhrase::class, 'Google_Service_Aiplatform_LearningGenaiRootScoredSimilarityTakedownPhrase');
