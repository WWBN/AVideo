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

namespace Google\Service\DatabaseMigrationService;

class BackgroundJobLogEntry extends \Google\Model
{
  /**
   * @var string
   */
  public $completionComment;
  /**
   * @var string
   */
  public $completionState;
  /**
   * @var string
   */
  public $finishTime;
  /**
   * @var string
   */
  public $id;
  protected $importRulesJobDetailsType = ImportRulesJobDetails::class;
  protected $importRulesJobDetailsDataType = '';
  public $importRulesJobDetails;
  /**
   * @var string
   */
  public $jobType;
  /**
   * @var bool
   */
  public $requestAutocommit;
  protected $seedJobDetailsType = SeedJobDetails::class;
  protected $seedJobDetailsDataType = '';
  public $seedJobDetails;
  /**
   * @var string
   */
  public $startTime;

  /**
   * @param string
   */
  public function setCompletionComment($completionComment)
  {
    $this->completionComment = $completionComment;
  }
  /**
   * @return string
   */
  public function getCompletionComment()
  {
    return $this->completionComment;
  }
  /**
   * @param string
   */
  public function setCompletionState($completionState)
  {
    $this->completionState = $completionState;
  }
  /**
   * @return string
   */
  public function getCompletionState()
  {
    return $this->completionState;
  }
  /**
   * @param string
   */
  public function setFinishTime($finishTime)
  {
    $this->finishTime = $finishTime;
  }
  /**
   * @return string
   */
  public function getFinishTime()
  {
    return $this->finishTime;
  }
  /**
   * @param string
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }
  /**
   * @param ImportRulesJobDetails
   */
  public function setImportRulesJobDetails(ImportRulesJobDetails $importRulesJobDetails)
  {
    $this->importRulesJobDetails = $importRulesJobDetails;
  }
  /**
   * @return ImportRulesJobDetails
   */
  public function getImportRulesJobDetails()
  {
    return $this->importRulesJobDetails;
  }
  /**
   * @param string
   */
  public function setJobType($jobType)
  {
    $this->jobType = $jobType;
  }
  /**
   * @return string
   */
  public function getJobType()
  {
    return $this->jobType;
  }
  /**
   * @param bool
   */
  public function setRequestAutocommit($requestAutocommit)
  {
    $this->requestAutocommit = $requestAutocommit;
  }
  /**
   * @return bool
   */
  public function getRequestAutocommit()
  {
    return $this->requestAutocommit;
  }
  /**
   * @param SeedJobDetails
   */
  public function setSeedJobDetails(SeedJobDetails $seedJobDetails)
  {
    $this->seedJobDetails = $seedJobDetails;
  }
  /**
   * @return SeedJobDetails
   */
  public function getSeedJobDetails()
  {
    return $this->seedJobDetails;
  }
  /**
   * @param string
   */
  public function setStartTime($startTime)
  {
    $this->startTime = $startTime;
  }
  /**
   * @return string
   */
  public function getStartTime()
  {
    return $this->startTime;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(BackgroundJobLogEntry::class, 'Google_Service_DatabaseMigrationService_BackgroundJobLogEntry');
