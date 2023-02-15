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

class SdrScrollToOnPageMatches extends \Google\Model
{
  /**
   * @var int
   */
  public $text;
  /**
   * @var int
   */
  public $textWithPrefix;
  /**
   * @var int
   */
  public $textWithPrefixSuffix;
  /**
   * @var int
   */
  public $textWithSuffix;

  /**
   * @param int
   */
  public function setText($text)
  {
    $this->text = $text;
  }
  /**
   * @return int
   */
  public function getText()
  {
    return $this->text;
  }
  /**
   * @param int
   */
  public function setTextWithPrefix($textWithPrefix)
  {
    $this->textWithPrefix = $textWithPrefix;
  }
  /**
   * @return int
   */
  public function getTextWithPrefix()
  {
    return $this->textWithPrefix;
  }
  /**
   * @param int
   */
  public function setTextWithPrefixSuffix($textWithPrefixSuffix)
  {
    $this->textWithPrefixSuffix = $textWithPrefixSuffix;
  }
  /**
   * @return int
   */
  public function getTextWithPrefixSuffix()
  {
    return $this->textWithPrefixSuffix;
  }
  /**
   * @param int
   */
  public function setTextWithSuffix($textWithSuffix)
  {
    $this->textWithSuffix = $textWithSuffix;
  }
  /**
   * @return int
   */
  public function getTextWithSuffix()
  {
    return $this->textWithSuffix;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SdrScrollToOnPageMatches::class, 'Google_Service_Contentwarehouse_SdrScrollToOnPageMatches');
