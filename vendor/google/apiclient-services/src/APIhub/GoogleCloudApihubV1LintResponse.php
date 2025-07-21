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

namespace Google\Service\APIhub;

class GoogleCloudApihubV1LintResponse extends \Google\Collection
{
  protected $collection_key = 'summary';
  /**
   * @var string
   */
  public $createTime;
  protected $issuesType = GoogleCloudApihubV1Issue::class;
  protected $issuesDataType = 'array';
  /**
   * @var string
   */
  public $linter;
  /**
   * @var string
   */
  public $source;
  /**
   * @var string
   */
  public $state;
  protected $summaryType = GoogleCloudApihubV1SummaryEntry::class;
  protected $summaryDataType = 'array';

  /**
   * @param string
   */
  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  /**
   * @return string
   */
  public function getCreateTime()
  {
    return $this->createTime;
  }
  /**
   * @param GoogleCloudApihubV1Issue[]
   */
  public function setIssues($issues)
  {
    $this->issues = $issues;
  }
  /**
   * @return GoogleCloudApihubV1Issue[]
   */
  public function getIssues()
  {
    return $this->issues;
  }
  /**
   * @param string
   */
  public function setLinter($linter)
  {
    $this->linter = $linter;
  }
  /**
   * @return string
   */
  public function getLinter()
  {
    return $this->linter;
  }
  /**
   * @param string
   */
  public function setSource($source)
  {
    $this->source = $source;
  }
  /**
   * @return string
   */
  public function getSource()
  {
    return $this->source;
  }
  /**
   * @param string
   */
  public function setState($state)
  {
    $this->state = $state;
  }
  /**
   * @return string
   */
  public function getState()
  {
    return $this->state;
  }
  /**
   * @param GoogleCloudApihubV1SummaryEntry[]
   */
  public function setSummary($summary)
  {
    $this->summary = $summary;
  }
  /**
   * @return GoogleCloudApihubV1SummaryEntry[]
   */
  public function getSummary()
  {
    return $this->summary;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1LintResponse::class, 'Google_Service_APIhub_GoogleCloudApihubV1LintResponse');
