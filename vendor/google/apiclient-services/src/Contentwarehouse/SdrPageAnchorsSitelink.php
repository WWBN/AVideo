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

class SdrPageAnchorsSitelink extends \Google\Model
{
  protected $embeddingType = SdrEmbedding::class;
  protected $embeddingDataType = '';
  public $embedding;
  /**
   * @var float
   */
  public $geometryScore;
  /**
   * @var float
   */
  public $headingAbbrvScore;
  /**
   * @var float
   */
  public $hpScore;
  /**
   * @var int
   */
  public $level;
  protected $scrollToType = SdrScrollTo::class;
  protected $scrollToDataType = '';
  public $scrollTo;
  /**
   * @var int
   */
  public $sectionHeight;
  /**
   * @var string
   */
  public $text;

  /**
   * @param SdrEmbedding
   */
  public function setEmbedding(SdrEmbedding $embedding)
  {
    $this->embedding = $embedding;
  }
  /**
   * @return SdrEmbedding
   */
  public function getEmbedding()
  {
    return $this->embedding;
  }
  /**
   * @param float
   */
  public function setGeometryScore($geometryScore)
  {
    $this->geometryScore = $geometryScore;
  }
  /**
   * @return float
   */
  public function getGeometryScore()
  {
    return $this->geometryScore;
  }
  /**
   * @param float
   */
  public function setHeadingAbbrvScore($headingAbbrvScore)
  {
    $this->headingAbbrvScore = $headingAbbrvScore;
  }
  /**
   * @return float
   */
  public function getHeadingAbbrvScore()
  {
    return $this->headingAbbrvScore;
  }
  /**
   * @param float
   */
  public function setHpScore($hpScore)
  {
    $this->hpScore = $hpScore;
  }
  /**
   * @return float
   */
  public function getHpScore()
  {
    return $this->hpScore;
  }
  /**
   * @param int
   */
  public function setLevel($level)
  {
    $this->level = $level;
  }
  /**
   * @return int
   */
  public function getLevel()
  {
    return $this->level;
  }
  /**
   * @param SdrScrollTo
   */
  public function setScrollTo(SdrScrollTo $scrollTo)
  {
    $this->scrollTo = $scrollTo;
  }
  /**
   * @return SdrScrollTo
   */
  public function getScrollTo()
  {
    return $this->scrollTo;
  }
  /**
   * @param int
   */
  public function setSectionHeight($sectionHeight)
  {
    $this->sectionHeight = $sectionHeight;
  }
  /**
   * @return int
   */
  public function getSectionHeight()
  {
    return $this->sectionHeight;
  }
  /**
   * @param string
   */
  public function setText($text)
  {
    $this->text = $text;
  }
  /**
   * @return string
   */
  public function getText()
  {
    return $this->text;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SdrPageAnchorsSitelink::class, 'Google_Service_Contentwarehouse_SdrPageAnchorsSitelink');
