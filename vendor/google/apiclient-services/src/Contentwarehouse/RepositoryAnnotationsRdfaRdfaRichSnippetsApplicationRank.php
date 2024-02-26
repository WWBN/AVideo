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

class RepositoryAnnotationsRdfaRdfaRichSnippetsApplicationRank extends \Google\Model
{
  /**
   * @var string
   */
  public $appStoreLink;
  /**
   * @var string
   */
  public $categoryId;
  /**
   * @var string
   */
  public $categoryName;
  /**
   * @var string
   */
  public $chartType;
  /**
   * @var string
   */
  public $rank;

  /**
   * @param string
   */
  public function setAppStoreLink($appStoreLink)
  {
    $this->appStoreLink = $appStoreLink;
  }
  /**
   * @return string
   */
  public function getAppStoreLink()
  {
    return $this->appStoreLink;
  }
  /**
   * @param string
   */
  public function setCategoryId($categoryId)
  {
    $this->categoryId = $categoryId;
  }
  /**
   * @return string
   */
  public function getCategoryId()
  {
    return $this->categoryId;
  }
  /**
   * @param string
   */
  public function setCategoryName($categoryName)
  {
    $this->categoryName = $categoryName;
  }
  /**
   * @return string
   */
  public function getCategoryName()
  {
    return $this->categoryName;
  }
  /**
   * @param string
   */
  public function setChartType($chartType)
  {
    $this->chartType = $chartType;
  }
  /**
   * @return string
   */
  public function getChartType()
  {
    return $this->chartType;
  }
  /**
   * @param string
   */
  public function setRank($rank)
  {
    $this->rank = $rank;
  }
  /**
   * @return string
   */
  public function getRank()
  {
    return $this->rank;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(RepositoryAnnotationsRdfaRdfaRichSnippetsApplicationRank::class, 'Google_Service_Contentwarehouse_RepositoryAnnotationsRdfaRdfaRichSnippetsApplicationRank');
