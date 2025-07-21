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

namespace Google\Service\StorageBatchOperations;

class Job extends \Google\Collection
{
  protected $collection_key = 'errorSummaries';
  protected $bucketListType = BucketList::class;
  protected $bucketListDataType = '';
  /**
   * @var string
   */
  public $completeTime;
  protected $countersType = Counters::class;
  protected $countersDataType = '';
  /**
   * @var string
   */
  public $createTime;
  protected $deleteObjectType = DeleteObject::class;
  protected $deleteObjectDataType = '';
  /**
   * @var string
   */
  public $description;
  protected $errorSummariesType = ErrorSummary::class;
  protected $errorSummariesDataType = 'array';
  protected $loggingConfigType = LoggingConfig::class;
  protected $loggingConfigDataType = '';
  /**
   * @var string
   */
  public $name;
  protected $putMetadataType = PutMetadata::class;
  protected $putMetadataDataType = '';
  protected $putObjectHoldType = PutObjectHold::class;
  protected $putObjectHoldDataType = '';
  protected $rewriteObjectType = RewriteObject::class;
  protected $rewriteObjectDataType = '';
  /**
   * @var string
   */
  public $scheduleTime;
  /**
   * @var string
   */
  public $state;

  /**
   * @param BucketList
   */
  public function setBucketList(BucketList $bucketList)
  {
    $this->bucketList = $bucketList;
  }
  /**
   * @return BucketList
   */
  public function getBucketList()
  {
    return $this->bucketList;
  }
  /**
   * @param string
   */
  public function setCompleteTime($completeTime)
  {
    $this->completeTime = $completeTime;
  }
  /**
   * @return string
   */
  public function getCompleteTime()
  {
    return $this->completeTime;
  }
  /**
   * @param Counters
   */
  public function setCounters(Counters $counters)
  {
    $this->counters = $counters;
  }
  /**
   * @return Counters
   */
  public function getCounters()
  {
    return $this->counters;
  }
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
   * @param DeleteObject
   */
  public function setDeleteObject(DeleteObject $deleteObject)
  {
    $this->deleteObject = $deleteObject;
  }
  /**
   * @return DeleteObject
   */
  public function getDeleteObject()
  {
    return $this->deleteObject;
  }
  /**
   * @param string
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }
  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }
  /**
   * @param ErrorSummary[]
   */
  public function setErrorSummaries($errorSummaries)
  {
    $this->errorSummaries = $errorSummaries;
  }
  /**
   * @return ErrorSummary[]
   */
  public function getErrorSummaries()
  {
    return $this->errorSummaries;
  }
  /**
   * @param LoggingConfig
   */
  public function setLoggingConfig(LoggingConfig $loggingConfig)
  {
    $this->loggingConfig = $loggingConfig;
  }
  /**
   * @return LoggingConfig
   */
  public function getLoggingConfig()
  {
    return $this->loggingConfig;
  }
  /**
   * @param string
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param PutMetadata
   */
  public function setPutMetadata(PutMetadata $putMetadata)
  {
    $this->putMetadata = $putMetadata;
  }
  /**
   * @return PutMetadata
   */
  public function getPutMetadata()
  {
    return $this->putMetadata;
  }
  /**
   * @param PutObjectHold
   */
  public function setPutObjectHold(PutObjectHold $putObjectHold)
  {
    $this->putObjectHold = $putObjectHold;
  }
  /**
   * @return PutObjectHold
   */
  public function getPutObjectHold()
  {
    return $this->putObjectHold;
  }
  /**
   * @param RewriteObject
   */
  public function setRewriteObject(RewriteObject $rewriteObject)
  {
    $this->rewriteObject = $rewriteObject;
  }
  /**
   * @return RewriteObject
   */
  public function getRewriteObject()
  {
    return $this->rewriteObject;
  }
  /**
   * @param string
   */
  public function setScheduleTime($scheduleTime)
  {
    $this->scheduleTime = $scheduleTime;
  }
  /**
   * @return string
   */
  public function getScheduleTime()
  {
    return $this->scheduleTime;
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
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Job::class, 'Google_Service_StorageBatchOperations_Job');
