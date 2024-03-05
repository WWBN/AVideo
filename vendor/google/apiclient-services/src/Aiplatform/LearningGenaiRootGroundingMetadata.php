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

class LearningGenaiRootGroundingMetadata extends \Google\Collection
{
  protected $collection_key = 'searchQueries';
  protected $citationsType = LearningGenaiRootGroundingMetadataCitation::class;
  protected $citationsDataType = 'array';
  /**
   * @var bool
   */
  public $groundingCancelled;
  /**
   * @var string[]
   */
  public $searchQueries;

  /**
   * @param LearningGenaiRootGroundingMetadataCitation[]
   */
  public function setCitations($citations)
  {
    $this->citations = $citations;
  }
  /**
   * @return LearningGenaiRootGroundingMetadataCitation[]
   */
  public function getCitations()
  {
    return $this->citations;
  }
  /**
   * @param bool
   */
  public function setGroundingCancelled($groundingCancelled)
  {
    $this->groundingCancelled = $groundingCancelled;
  }
  /**
   * @return bool
   */
  public function getGroundingCancelled()
  {
    return $this->groundingCancelled;
  }
  /**
   * @param string[]
   */
  public function setSearchQueries($searchQueries)
  {
    $this->searchQueries = $searchQueries;
  }
  /**
   * @return string[]
   */
  public function getSearchQueries()
  {
    return $this->searchQueries;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootGroundingMetadata::class, 'Google_Service_Aiplatform_LearningGenaiRootGroundingMetadata');
