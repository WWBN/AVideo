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

class LearningGenaiRootRoutingDecisionMetadataScoreBased extends \Google\Model
{
  protected $matchedRuleType = LearningGenaiRootScoreBasedRoutingConfigRule::class;
  protected $matchedRuleDataType = '';
  protected $scoreType = LearningGenaiRootScore::class;
  protected $scoreDataType = '';
  /**
   * @var bool
   */
  public $usedDefaultFallback;

  /**
   * @param LearningGenaiRootScoreBasedRoutingConfigRule
   */
  public function setMatchedRule(LearningGenaiRootScoreBasedRoutingConfigRule $matchedRule)
  {
    $this->matchedRule = $matchedRule;
  }
  /**
   * @return LearningGenaiRootScoreBasedRoutingConfigRule
   */
  public function getMatchedRule()
  {
    return $this->matchedRule;
  }
  /**
   * @param LearningGenaiRootScore
   */
  public function setScore(LearningGenaiRootScore $score)
  {
    $this->score = $score;
  }
  /**
   * @return LearningGenaiRootScore
   */
  public function getScore()
  {
    return $this->score;
  }
  /**
   * @param bool
   */
  public function setUsedDefaultFallback($usedDefaultFallback)
  {
    $this->usedDefaultFallback = $usedDefaultFallback;
  }
  /**
   * @return bool
   */
  public function getUsedDefaultFallback()
  {
    return $this->usedDefaultFallback;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootRoutingDecisionMetadataScoreBased::class, 'Google_Service_Aiplatform_LearningGenaiRootRoutingDecisionMetadataScoreBased');
