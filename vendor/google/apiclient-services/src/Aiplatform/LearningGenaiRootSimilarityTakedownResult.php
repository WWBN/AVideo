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

class LearningGenaiRootSimilarityTakedownResult extends \Google\Collection
{
  protected $collection_key = 'scoredPhrases';
  /**
   * @var bool
   */
  public $allowed;
  protected $scoredPhrasesType = LearningGenaiRootScoredSimilarityTakedownPhrase::class;
  protected $scoredPhrasesDataType = 'array';

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
   * @param LearningGenaiRootScoredSimilarityTakedownPhrase[]
   */
  public function setScoredPhrases($scoredPhrases)
  {
    $this->scoredPhrases = $scoredPhrases;
  }
  /**
   * @return LearningGenaiRootScoredSimilarityTakedownPhrase[]
   */
  public function getScoredPhrases()
  {
    return $this->scoredPhrases;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootSimilarityTakedownResult::class, 'Google_Service_Aiplatform_LearningGenaiRootSimilarityTakedownResult');
