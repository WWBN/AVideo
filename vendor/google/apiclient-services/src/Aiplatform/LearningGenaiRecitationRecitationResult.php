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

class LearningGenaiRecitationRecitationResult extends \Google\Collection
{
  protected $collection_key = 'trainingSegmentResults';
  protected $dynamicSegmentResultsType = LearningGenaiRecitationSegmentResult::class;
  protected $dynamicSegmentResultsDataType = 'array';
  /**
   * @var string
   */
  public $recitationAction;
  protected $trainingSegmentResultsType = LearningGenaiRecitationSegmentResult::class;
  protected $trainingSegmentResultsDataType = 'array';

  /**
   * @param LearningGenaiRecitationSegmentResult[]
   */
  public function setDynamicSegmentResults($dynamicSegmentResults)
  {
    $this->dynamicSegmentResults = $dynamicSegmentResults;
  }
  /**
   * @return LearningGenaiRecitationSegmentResult[]
   */
  public function getDynamicSegmentResults()
  {
    return $this->dynamicSegmentResults;
  }
  /**
   * @param string
   */
  public function setRecitationAction($recitationAction)
  {
    $this->recitationAction = $recitationAction;
  }
  /**
   * @return string
   */
  public function getRecitationAction()
  {
    return $this->recitationAction;
  }
  /**
   * @param LearningGenaiRecitationSegmentResult[]
   */
  public function setTrainingSegmentResults($trainingSegmentResults)
  {
    $this->trainingSegmentResults = $trainingSegmentResults;
  }
  /**
   * @return LearningGenaiRecitationSegmentResult[]
   */
  public function getTrainingSegmentResults()
  {
    return $this->trainingSegmentResults;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRecitationRecitationResult::class, 'Google_Service_Aiplatform_LearningGenaiRecitationRecitationResult');
