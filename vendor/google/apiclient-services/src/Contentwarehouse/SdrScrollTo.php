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

class SdrScrollTo extends \Google\Model
{
  protected $onpageMatchesType = SdrScrollToOnPageMatches::class;
  protected $onpageMatchesDataType = '';
  /**
   * @var string
   */
  public $prefix;
  /**
   * @var string
   */
  public $suffix;
  /**
   * @var string
   */
  public $textEnd;
  /**
   * @var string
   */
  public $textStart;

  /**
   * @param SdrScrollToOnPageMatches
   */
  public function setOnpageMatches(SdrScrollToOnPageMatches $onpageMatches)
  {
    $this->onpageMatches = $onpageMatches;
  }
  /**
   * @return SdrScrollToOnPageMatches
   */
  public function getOnpageMatches()
  {
    return $this->onpageMatches;
  }
  /**
   * @param string
   */
  public function setPrefix($prefix)
  {
    $this->prefix = $prefix;
  }
  /**
   * @return string
   */
  public function getPrefix()
  {
    return $this->prefix;
  }
  /**
   * @param string
   */
  public function setSuffix($suffix)
  {
    $this->suffix = $suffix;
  }
  /**
   * @return string
   */
  public function getSuffix()
  {
    return $this->suffix;
  }
  /**
   * @param string
   */
  public function setTextEnd($textEnd)
  {
    $this->textEnd = $textEnd;
  }
  /**
   * @return string
   */
  public function getTextEnd()
  {
    return $this->textEnd;
  }
  /**
   * @param string
   */
  public function setTextStart($textStart)
  {
    $this->textStart = $textStart;
  }
  /**
   * @return string
   */
  public function getTextStart()
  {
    return $this->textStart;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SdrScrollTo::class, 'Google_Service_Contentwarehouse_SdrScrollTo');
