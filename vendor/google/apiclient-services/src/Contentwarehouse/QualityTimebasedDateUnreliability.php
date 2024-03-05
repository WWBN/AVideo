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

class QualityTimebasedDateUnreliability extends \Google\Collection
{
  protected $collection_key = 'petacatScores';
  protected $petacatScoresType = QualityTimebasedPetacatDateUnreliability::class;
  protected $petacatScoresDataType = 'array';
  /**
   * @var float
   */
  public $unreliableDatesScore;
  /**
   * @var float
   */
  public $unreliableDatesScoreExposureAdjusted;

  /**
   * @param QualityTimebasedPetacatDateUnreliability[]
   */
  public function setPetacatScores($petacatScores)
  {
    $this->petacatScores = $petacatScores;
  }
  /**
   * @return QualityTimebasedPetacatDateUnreliability[]
   */
  public function getPetacatScores()
  {
    return $this->petacatScores;
  }
  /**
   * @param float
   */
  public function setUnreliableDatesScore($unreliableDatesScore)
  {
    $this->unreliableDatesScore = $unreliableDatesScore;
  }
  /**
   * @return float
   */
  public function getUnreliableDatesScore()
  {
    return $this->unreliableDatesScore;
  }
  /**
   * @param float
   */
  public function setUnreliableDatesScoreExposureAdjusted($unreliableDatesScoreExposureAdjusted)
  {
    $this->unreliableDatesScoreExposureAdjusted = $unreliableDatesScoreExposureAdjusted;
  }
  /**
   * @return float
   */
  public function getUnreliableDatesScoreExposureAdjusted()
  {
    return $this->unreliableDatesScoreExposureAdjusted;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(QualityTimebasedDateUnreliability::class, 'Google_Service_Contentwarehouse_QualityTimebasedDateUnreliability');
