<?php
/*
 * Copyright 2016 Google Inc.
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

class Google_Service_Fitness_AggregateRequest extends Google_Collection
{
  protected $collection_key = 'filteredDataQualityStandard';
  protected $aggregateByType = 'Google_Service_Fitness_AggregateBy';
  protected $aggregateByDataType = 'array';
  protected $bucketByActivitySegmentType = 'Google_Service_Fitness_BucketByActivity';
  protected $bucketByActivitySegmentDataType = '';
  protected $bucketByActivityTypeType = 'Google_Service_Fitness_BucketByActivity';
  protected $bucketByActivityTypeDataType = '';
  protected $bucketBySessionType = 'Google_Service_Fitness_BucketBySession';
  protected $bucketBySessionDataType = '';
  protected $bucketByTimeType = 'Google_Service_Fitness_BucketByTime';
  protected $bucketByTimeDataType = '';
  public $endTimeMillis;
  public $filteredDataQualityStandard;
  public $startTimeMillis;

  public function setAggregateBy($aggregateBy)
  {
    $this->aggregateBy = $aggregateBy;
  }
  public function getAggregateBy()
  {
    return $this->aggregateBy;
  }
  public function setBucketByActivitySegment(Google_Service_Fitness_BucketByActivity $bucketByActivitySegment)
  {
    $this->bucketByActivitySegment = $bucketByActivitySegment;
  }
  public function getBucketByActivitySegment()
  {
    return $this->bucketByActivitySegment;
  }
  public function setBucketByActivityType(Google_Service_Fitness_BucketByActivity $bucketByActivityType)
  {
    $this->bucketByActivityType = $bucketByActivityType;
  }
  public function getBucketByActivityType()
  {
    return $this->bucketByActivityType;
  }
  public function setBucketBySession(Google_Service_Fitness_BucketBySession $bucketBySession)
  {
    $this->bucketBySession = $bucketBySession;
  }
  public function getBucketBySession()
  {
    return $this->bucketBySession;
  }
  public function setBucketByTime(Google_Service_Fitness_BucketByTime $bucketByTime)
  {
    $this->bucketByTime = $bucketByTime;
  }
  public function getBucketByTime()
  {
    return $this->bucketByTime;
  }
  public function setEndTimeMillis($endTimeMillis)
  {
    $this->endTimeMillis = $endTimeMillis;
  }
  public function getEndTimeMillis()
  {
    return $this->endTimeMillis;
  }
  public function setFilteredDataQualityStandard($filteredDataQualityStandard)
  {
    $this->filteredDataQualityStandard = $filteredDataQualityStandard;
  }
  public function getFilteredDataQualityStandard()
  {
    return $this->filteredDataQualityStandard;
  }
  public function setStartTimeMillis($startTimeMillis)
  {
    $this->startTimeMillis = $startTimeMillis;
  }
  public function getStartTimeMillis()
  {
    return $this->startTimeMillis;
  }
}
