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

class RepositoryWebrefPerDocRelevanceRating extends \Google\Model
{
  /**
   * @var string
   */
  public $contentRelevant;
  /**
   * @var string
   */
  public $displayString;
  /**
   * @var string
   */
  public $furballUrl;
  /**
   * @var string
   */
  public $itemId;
  /**
   * @var string
   */
  public $pageIsAboutChain;
  /**
   * @var string
   */
  public $projectId;
  /**
   * @var bool
   */
  public $raterCanUnderstandTopic;
  protected $taskDetailsType = RepositoryWebrefTaskDetails::class;
  protected $taskDetailsDataType = '';
  /**
   * @var string
   */
  public $taskId;
  /**
   * @var string
   */
  public $topicIsChain;

  /**
   * @param string
   */
  public function setContentRelevant($contentRelevant)
  {
    $this->contentRelevant = $contentRelevant;
  }
  /**
   * @return string
   */
  public function getContentRelevant()
  {
    return $this->contentRelevant;
  }
  /**
   * @param string
   */
  public function setDisplayString($displayString)
  {
    $this->displayString = $displayString;
  }
  /**
   * @return string
   */
  public function getDisplayString()
  {
    return $this->displayString;
  }
  /**
   * @param string
   */
  public function setFurballUrl($furballUrl)
  {
    $this->furballUrl = $furballUrl;
  }
  /**
   * @return string
   */
  public function getFurballUrl()
  {
    return $this->furballUrl;
  }
  /**
   * @param string
   */
  public function setItemId($itemId)
  {
    $this->itemId = $itemId;
  }
  /**
   * @return string
   */
  public function getItemId()
  {
    return $this->itemId;
  }
  /**
   * @param string
   */
  public function setPageIsAboutChain($pageIsAboutChain)
  {
    $this->pageIsAboutChain = $pageIsAboutChain;
  }
  /**
   * @return string
   */
  public function getPageIsAboutChain()
  {
    return $this->pageIsAboutChain;
  }
  /**
   * @param string
   */
  public function setProjectId($projectId)
  {
    $this->projectId = $projectId;
  }
  /**
   * @return string
   */
  public function getProjectId()
  {
    return $this->projectId;
  }
  /**
   * @param bool
   */
  public function setRaterCanUnderstandTopic($raterCanUnderstandTopic)
  {
    $this->raterCanUnderstandTopic = $raterCanUnderstandTopic;
  }
  /**
   * @return bool
   */
  public function getRaterCanUnderstandTopic()
  {
    return $this->raterCanUnderstandTopic;
  }
  /**
   * @param RepositoryWebrefTaskDetails
   */
  public function setTaskDetails(RepositoryWebrefTaskDetails $taskDetails)
  {
    $this->taskDetails = $taskDetails;
  }
  /**
   * @return RepositoryWebrefTaskDetails
   */
  public function getTaskDetails()
  {
    return $this->taskDetails;
  }
  /**
   * @param string
   */
  public function setTaskId($taskId)
  {
    $this->taskId = $taskId;
  }
  /**
   * @return string
   */
  public function getTaskId()
  {
    return $this->taskId;
  }
  /**
   * @param string
   */
  public function setTopicIsChain($topicIsChain)
  {
    $this->topicIsChain = $topicIsChain;
  }
  /**
   * @return string
   */
  public function getTopicIsChain()
  {
    return $this->topicIsChain;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(RepositoryWebrefPerDocRelevanceRating::class, 'Google_Service_Contentwarehouse_RepositoryWebrefPerDocRelevanceRating');
