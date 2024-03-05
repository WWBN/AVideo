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

class AssistantGroundingRankerPodcastGroundingProviderFeatures extends \Google\Model
{
  /**
   * @var bool
   */
  public $isExclusive;
  /**
   * @var int
   */
  public $podcastListenersCount;
  public $popularityScore;
  /**
   * @var float
   */
  public $scubedNg3ModelScore;
  public $scubedTstarScore;
  public $youtubeConfidenceScore;

  /**
   * @param bool
   */
  public function setIsExclusive($isExclusive)
  {
    $this->isExclusive = $isExclusive;
  }
  /**
   * @return bool
   */
  public function getIsExclusive()
  {
    return $this->isExclusive;
  }
  /**
   * @param int
   */
  public function setPodcastListenersCount($podcastListenersCount)
  {
    $this->podcastListenersCount = $podcastListenersCount;
  }
  /**
   * @return int
   */
  public function getPodcastListenersCount()
  {
    return $this->podcastListenersCount;
  }
  public function setPopularityScore($popularityScore)
  {
    $this->popularityScore = $popularityScore;
  }
  public function getPopularityScore()
  {
    return $this->popularityScore;
  }
  /**
   * @param float
   */
  public function setScubedNg3ModelScore($scubedNg3ModelScore)
  {
    $this->scubedNg3ModelScore = $scubedNg3ModelScore;
  }
  /**
   * @return float
   */
  public function getScubedNg3ModelScore()
  {
    return $this->scubedNg3ModelScore;
  }
  public function setScubedTstarScore($scubedTstarScore)
  {
    $this->scubedTstarScore = $scubedTstarScore;
  }
  public function getScubedTstarScore()
  {
    return $this->scubedTstarScore;
  }
  public function setYoutubeConfidenceScore($youtubeConfidenceScore)
  {
    $this->youtubeConfidenceScore = $youtubeConfidenceScore;
  }
  public function getYoutubeConfidenceScore()
  {
    return $this->youtubeConfidenceScore;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantGroundingRankerPodcastGroundingProviderFeatures::class, 'Google_Service_Contentwarehouse_AssistantGroundingRankerPodcastGroundingProviderFeatures');
