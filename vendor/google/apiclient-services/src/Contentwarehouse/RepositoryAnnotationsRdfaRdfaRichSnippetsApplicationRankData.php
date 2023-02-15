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

class RepositoryAnnotationsRdfaRdfaRichSnippetsApplicationRankData extends \Google\Collection
{
  protected $collection_key = 'rank';
  /**
   * @var string[]
   */
  public $playwrightCategoryId;
  protected $rankType = RepositoryAnnotationsRdfaRdfaRichSnippetsApplicationRank::class;
  protected $rankDataType = 'array';
  public $rank;

  /**
   * @param string[]
   */
  public function setPlaywrightCategoryId($playwrightCategoryId)
  {
    $this->playwrightCategoryId = $playwrightCategoryId;
  }
  /**
   * @return string[]
   */
  public function getPlaywrightCategoryId()
  {
    return $this->playwrightCategoryId;
  }
  /**
   * @param RepositoryAnnotationsRdfaRdfaRichSnippetsApplicationRank[]
   */
  public function setRank($rank)
  {
    $this->rank = $rank;
  }
  /**
   * @return RepositoryAnnotationsRdfaRdfaRichSnippetsApplicationRank[]
   */
  public function getRank()
  {
    return $this->rank;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(RepositoryAnnotationsRdfaRdfaRichSnippetsApplicationRankData::class, 'Google_Service_Contentwarehouse_RepositoryAnnotationsRdfaRdfaRichSnippetsApplicationRankData');
