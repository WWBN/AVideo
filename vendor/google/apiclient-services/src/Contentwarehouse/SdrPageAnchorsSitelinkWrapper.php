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

class SdrPageAnchorsSitelinkWrapper extends \Google\Model
{
  /**
   * @var string
   */
  public $abbreviatedHeadingText;
  protected $abbrvEmbeddingType = SdrEmbedding::class;
  protected $abbrvEmbeddingDataType = '';
  protected $headingEmbeddingType = SdrEmbedding::class;
  protected $headingEmbeddingDataType = '';
  /**
   * @var string
   */
  public $normalizedHeadingText;
  protected $passageEmbeddingType = SdrEmbedding::class;
  protected $passageEmbeddingDataType = '';
  /**
   * @var string
   */
  public $passageText;

  /**
   * @param string
   */
  public function setAbbreviatedHeadingText($abbreviatedHeadingText)
  {
    $this->abbreviatedHeadingText = $abbreviatedHeadingText;
  }
  /**
   * @return string
   */
  public function getAbbreviatedHeadingText()
  {
    return $this->abbreviatedHeadingText;
  }
  /**
   * @param SdrEmbedding
   */
  public function setAbbrvEmbedding(SdrEmbedding $abbrvEmbedding)
  {
    $this->abbrvEmbedding = $abbrvEmbedding;
  }
  /**
   * @return SdrEmbedding
   */
  public function getAbbrvEmbedding()
  {
    return $this->abbrvEmbedding;
  }
  /**
   * @param SdrEmbedding
   */
  public function setHeadingEmbedding(SdrEmbedding $headingEmbedding)
  {
    $this->headingEmbedding = $headingEmbedding;
  }
  /**
   * @return SdrEmbedding
   */
  public function getHeadingEmbedding()
  {
    return $this->headingEmbedding;
  }
  /**
   * @param string
   */
  public function setNormalizedHeadingText($normalizedHeadingText)
  {
    $this->normalizedHeadingText = $normalizedHeadingText;
  }
  /**
   * @return string
   */
  public function getNormalizedHeadingText()
  {
    return $this->normalizedHeadingText;
  }
  /**
   * @param SdrEmbedding
   */
  public function setPassageEmbedding(SdrEmbedding $passageEmbedding)
  {
    $this->passageEmbedding = $passageEmbedding;
  }
  /**
   * @return SdrEmbedding
   */
  public function getPassageEmbedding()
  {
    return $this->passageEmbedding;
  }
  /**
   * @param string
   */
  public function setPassageText($passageText)
  {
    $this->passageText = $passageText;
  }
  /**
   * @return string
   */
  public function getPassageText()
  {
    return $this->passageText;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SdrPageAnchorsSitelinkWrapper::class, 'Google_Service_Contentwarehouse_SdrPageAnchorsSitelinkWrapper');
