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

class SnippetExtraInfoSnippetsBrainModelInfo extends \Google\Model
{
  /**
   * @var string
   */
  public $ng3ModelName;
  /**
   * @var string
   */
  public $snippetsbrainModelName;
  /**
   * @var string
   */
  public $snippetsbrainModelPartition;
  /**
   * @var string
   */
  public $snippetsbrainTokenizerType;

  /**
   * @param string
   */
  public function setNg3ModelName($ng3ModelName)
  {
    $this->ng3ModelName = $ng3ModelName;
  }
  /**
   * @return string
   */
  public function getNg3ModelName()
  {
    return $this->ng3ModelName;
  }
  /**
   * @param string
   */
  public function setSnippetsbrainModelName($snippetsbrainModelName)
  {
    $this->snippetsbrainModelName = $snippetsbrainModelName;
  }
  /**
   * @return string
   */
  public function getSnippetsbrainModelName()
  {
    return $this->snippetsbrainModelName;
  }
  /**
   * @param string
   */
  public function setSnippetsbrainModelPartition($snippetsbrainModelPartition)
  {
    $this->snippetsbrainModelPartition = $snippetsbrainModelPartition;
  }
  /**
   * @return string
   */
  public function getSnippetsbrainModelPartition()
  {
    return $this->snippetsbrainModelPartition;
  }
  /**
   * @param string
   */
  public function setSnippetsbrainTokenizerType($snippetsbrainTokenizerType)
  {
    $this->snippetsbrainTokenizerType = $snippetsbrainTokenizerType;
  }
  /**
   * @return string
   */
  public function getSnippetsbrainTokenizerType()
  {
    return $this->snippetsbrainTokenizerType;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SnippetExtraInfoSnippetsBrainModelInfo::class, 'Google_Service_Contentwarehouse_SnippetExtraInfoSnippetsBrainModelInfo');
