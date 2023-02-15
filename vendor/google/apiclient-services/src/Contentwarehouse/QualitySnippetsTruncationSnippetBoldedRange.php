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

class QualitySnippetsTruncationSnippetBoldedRange extends \Google\Model
{
  protected $beginType = QualitySnippetsTruncationSnippetBoldedRangePosition::class;
  protected $beginDataType = '';
  public $begin;
  protected $endType = QualitySnippetsTruncationSnippetBoldedRangePosition::class;
  protected $endDataType = '';
  public $end;
  /**
   * @var string
   */
  public $text;
  /**
   * @var string
   */
  public $type;

  /**
   * @param QualitySnippetsTruncationSnippetBoldedRangePosition
   */
  public function setBegin(QualitySnippetsTruncationSnippetBoldedRangePosition $begin)
  {
    $this->begin = $begin;
  }
  /**
   * @return QualitySnippetsTruncationSnippetBoldedRangePosition
   */
  public function getBegin()
  {
    return $this->begin;
  }
  /**
   * @param QualitySnippetsTruncationSnippetBoldedRangePosition
   */
  public function setEnd(QualitySnippetsTruncationSnippetBoldedRangePosition $end)
  {
    $this->end = $end;
  }
  /**
   * @return QualitySnippetsTruncationSnippetBoldedRangePosition
   */
  public function getEnd()
  {
    return $this->end;
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
  /**
   * @param string
   */
  public function setType($type)
  {
    $this->type = $type;
  }
  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(QualitySnippetsTruncationSnippetBoldedRange::class, 'Google_Service_Contentwarehouse_QualitySnippetsTruncationSnippetBoldedRange');
