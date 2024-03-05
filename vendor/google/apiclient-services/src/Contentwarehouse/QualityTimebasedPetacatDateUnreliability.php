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

class QualityTimebasedPetacatDateUnreliability extends \Google\Model
{
  /**
   * @var float
   */
  public $contentageReliability;
  /**
   * @var float
   */
  public $dateExposure;
  /**
   * @var float
   */
  public $dateExposureScore;
  /**
   * @var float
   */
  public $dateVsContentageDistributionSkew;
  /**
   * @var float
   */
  public $isForumQnaSocialMediaProbability;
  /**
   * @var int
   */
  public $petacatId;
  /**
   * @var float
   */
  public $unreliableDatesScore;

  /**
   * @param float
   */
  public function setContentageReliability($contentageReliability)
  {
    $this->contentageReliability = $contentageReliability;
  }
  /**
   * @return float
   */
  public function getContentageReliability()
  {
    return $this->contentageReliability;
  }
  /**
   * @param float
   */
  public function setDateExposure($dateExposure)
  {
    $this->dateExposure = $dateExposure;
  }
  /**
   * @return float
   */
  public function getDateExposure()
  {
    return $this->dateExposure;
  }
  /**
   * @param float
   */
  public function setDateExposureScore($dateExposureScore)
  {
    $this->dateExposureScore = $dateExposureScore;
  }
  /**
   * @return float
   */
  public function getDateExposureScore()
  {
    return $this->dateExposureScore;
  }
  /**
   * @param float
   */
  public function setDateVsContentageDistributionSkew($dateVsContentageDistributionSkew)
  {
    $this->dateVsContentageDistributionSkew = $dateVsContentageDistributionSkew;
  }
  /**
   * @return float
   */
  public function getDateVsContentageDistributionSkew()
  {
    return $this->dateVsContentageDistributionSkew;
  }
  /**
   * @param float
   */
  public function setIsForumQnaSocialMediaProbability($isForumQnaSocialMediaProbability)
  {
    $this->isForumQnaSocialMediaProbability = $isForumQnaSocialMediaProbability;
  }
  /**
   * @return float
   */
  public function getIsForumQnaSocialMediaProbability()
  {
    return $this->isForumQnaSocialMediaProbability;
  }
  /**
   * @param int
   */
  public function setPetacatId($petacatId)
  {
    $this->petacatId = $petacatId;
  }
  /**
   * @return int
   */
  public function getPetacatId()
  {
    return $this->petacatId;
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
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(QualityTimebasedPetacatDateUnreliability::class, 'Google_Service_Contentwarehouse_QualityTimebasedPetacatDateUnreliability');
