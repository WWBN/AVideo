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

class RepositoryWebrefReferencePageScores extends \Google\Model
{
  /**
   * @var float
   */
  public $bookScore;
  /**
   * @var float
   */
  public $referencePageScore;
  /**
   * @var bool
   */
  public $selected;
  /**
   * @var float
   */
  public $singleTopicness;
  /**
   * @var float
   */
  public $singleTopicnessV2;

  /**
   * @param float
   */
  public function setBookScore($bookScore)
  {
    $this->bookScore = $bookScore;
  }
  /**
   * @return float
   */
  public function getBookScore()
  {
    return $this->bookScore;
  }
  /**
   * @param float
   */
  public function setReferencePageScore($referencePageScore)
  {
    $this->referencePageScore = $referencePageScore;
  }
  /**
   * @return float
   */
  public function getReferencePageScore()
  {
    return $this->referencePageScore;
  }
  /**
   * @param bool
   */
  public function setSelected($selected)
  {
    $this->selected = $selected;
  }
  /**
   * @return bool
   */
  public function getSelected()
  {
    return $this->selected;
  }
  /**
   * @param float
   */
  public function setSingleTopicness($singleTopicness)
  {
    $this->singleTopicness = $singleTopicness;
  }
  /**
   * @return float
   */
  public function getSingleTopicness()
  {
    return $this->singleTopicness;
  }
  /**
   * @param float
   */
  public function setSingleTopicnessV2($singleTopicnessV2)
  {
    $this->singleTopicnessV2 = $singleTopicnessV2;
  }
  /**
   * @return float
   */
  public function getSingleTopicnessV2()
  {
    return $this->singleTopicnessV2;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(RepositoryWebrefReferencePageScores::class, 'Google_Service_Contentwarehouse_RepositoryWebrefReferencePageScores');
