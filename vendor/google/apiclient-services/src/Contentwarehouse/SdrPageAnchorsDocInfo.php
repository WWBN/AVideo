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

class SdrPageAnchorsDocInfo extends \Google\Collection
{
  protected $collection_key = 'sitelinkWrapper';
  /**
   * @var float
   */
  public $articleness;
  protected $pageAnchorsType = SdrPageAnchorsSitelink::class;
  protected $pageAnchorsDataType = 'array';
  /**
   * @var float
   */
  public $qscore;
  protected $sitelinkWrapperType = SdrPageAnchorsSitelinkWrapper::class;
  protected $sitelinkWrapperDataType = 'array';
  /**
   * @var float
   */
  public $textRichness;

  /**
   * @param float
   */
  public function setArticleness($articleness)
  {
    $this->articleness = $articleness;
  }
  /**
   * @return float
   */
  public function getArticleness()
  {
    return $this->articleness;
  }
  /**
   * @param SdrPageAnchorsSitelink[]
   */
  public function setPageAnchors($pageAnchors)
  {
    $this->pageAnchors = $pageAnchors;
  }
  /**
   * @return SdrPageAnchorsSitelink[]
   */
  public function getPageAnchors()
  {
    return $this->pageAnchors;
  }
  /**
   * @param float
   */
  public function setQscore($qscore)
  {
    $this->qscore = $qscore;
  }
  /**
   * @return float
   */
  public function getQscore()
  {
    return $this->qscore;
  }
  /**
   * @param SdrPageAnchorsSitelinkWrapper[]
   */
  public function setSitelinkWrapper($sitelinkWrapper)
  {
    $this->sitelinkWrapper = $sitelinkWrapper;
  }
  /**
   * @return SdrPageAnchorsSitelinkWrapper[]
   */
  public function getSitelinkWrapper()
  {
    return $this->sitelinkWrapper;
  }
  /**
   * @param float
   */
  public function setTextRichness($textRichness)
  {
    $this->textRichness = $textRichness;
  }
  /**
   * @return float
   */
  public function getTextRichness()
  {
    return $this->textRichness;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SdrPageAnchorsDocInfo::class, 'Google_Service_Contentwarehouse_SdrPageAnchorsDocInfo');
