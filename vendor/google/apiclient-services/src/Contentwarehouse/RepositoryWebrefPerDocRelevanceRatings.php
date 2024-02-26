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

class RepositoryWebrefPerDocRelevanceRatings extends \Google\Collection
{
  protected $collection_key = 'taskLevelRating';
  /**
   * @var string
   */
  public $docFp;
  protected $entityNameRatingType = RepositoryWebrefEntityNameRatings::class;
  protected $entityNameRatingDataType = 'array';
  protected $mentionRatingType = RepositoryWebrefMentionRatings::class;
  protected $mentionRatingDataType = 'array';
  protected $taskLevelRatingType = RepositoryWebrefPerDocRelevanceRating::class;
  protected $taskLevelRatingDataType = 'array';
  /**
   * @var string
   */
  public $url;

  /**
   * @param string
   */
  public function setDocFp($docFp)
  {
    $this->docFp = $docFp;
  }
  /**
   * @return string
   */
  public function getDocFp()
  {
    return $this->docFp;
  }
  /**
   * @param RepositoryWebrefEntityNameRatings[]
   */
  public function setEntityNameRating($entityNameRating)
  {
    $this->entityNameRating = $entityNameRating;
  }
  /**
   * @return RepositoryWebrefEntityNameRatings[]
   */
  public function getEntityNameRating()
  {
    return $this->entityNameRating;
  }
  /**
   * @param RepositoryWebrefMentionRatings[]
   */
  public function setMentionRating($mentionRating)
  {
    $this->mentionRating = $mentionRating;
  }
  /**
   * @return RepositoryWebrefMentionRatings[]
   */
  public function getMentionRating()
  {
    return $this->mentionRating;
  }
  /**
   * @param RepositoryWebrefPerDocRelevanceRating[]
   */
  public function setTaskLevelRating($taskLevelRating)
  {
    $this->taskLevelRating = $taskLevelRating;
  }
  /**
   * @return RepositoryWebrefPerDocRelevanceRating[]
   */
  public function getTaskLevelRating()
  {
    return $this->taskLevelRating;
  }
  /**
   * @param string
   */
  public function setUrl($url)
  {
    $this->url = $url;
  }
  /**
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(RepositoryWebrefPerDocRelevanceRatings::class, 'Google_Service_Contentwarehouse_RepositoryWebrefPerDocRelevanceRatings');
