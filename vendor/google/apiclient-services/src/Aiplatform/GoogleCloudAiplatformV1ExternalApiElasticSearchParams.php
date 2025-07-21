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

class GoogleCloudAiplatformV1ExternalApiElasticSearchParams extends \Google\Model
{
  /**
   * @var string
   */
  public $index;
  /**
   * @var int
   */
  public $numHits;
  /**
   * @var string
   */
  public $searchTemplate;

  /**
   * @param string
   */
  public function setIndex($index)
  {
    $this->index = $index;
  }
  /**
   * @return string
   */
  public function getIndex()
  {
    return $this->index;
  }
  /**
   * @param int
   */
  public function setNumHits($numHits)
  {
    $this->numHits = $numHits;
  }
  /**
   * @return int
   */
  public function getNumHits()
  {
    return $this->numHits;
  }
  /**
   * @param string
   */
  public function setSearchTemplate($searchTemplate)
  {
    $this->searchTemplate = $searchTemplate;
  }
  /**
   * @return string
   */
  public function getSearchTemplate()
  {
    return $this->searchTemplate;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudAiplatformV1ExternalApiElasticSearchParams::class, 'Google_Service_Aiplatform_GoogleCloudAiplatformV1ExternalApiElasticSearchParams');
