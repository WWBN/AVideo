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

class Google_Service_Dataflow_WorkItemStatus extends Google_Collection
{
  protected $collection_key = 'metricUpdates';
  public $completed;
  protected $counterUpdatesType = 'Google_Service_Dataflow_CounterUpdate';
  protected $counterUpdatesDataType = 'array';
  protected $dynamicSourceSplitType = 'Google_Service_Dataflow_DynamicSourceSplit';
  protected $dynamicSourceSplitDataType = '';
  protected $errorsType = 'Google_Service_Dataflow_Status';
  protected $errorsDataType = 'array';
  protected $metricUpdatesType = 'Google_Service_Dataflow_MetricUpdate';
  protected $metricUpdatesDataType = 'array';
  protected $progressType = 'Google_Service_Dataflow_ApproximateProgress';
  protected $progressDataType = '';
  public $reportIndex;
  protected $reportedProgressType = 'Google_Service_Dataflow_ApproximateReportedProgress';
  protected $reportedProgressDataType = '';
  public $requestedLeaseDuration;
  protected $sourceForkType = 'Google_Service_Dataflow_SourceFork';
  protected $sourceForkDataType = '';
  protected $sourceOperationResponseType = 'Google_Service_Dataflow_SourceOperationResponse';
  protected $sourceOperationResponseDataType = '';
  protected $stopPositionType = 'Google_Service_Dataflow_Position';
  protected $stopPositionDataType = '';
  public $workItemId;

  public function setCompleted($completed)
  {
    $this->completed = $completed;
  }
  public function getCompleted()
  {
    return $this->completed;
  }
  public function setCounterUpdates($counterUpdates)
  {
    $this->counterUpdates = $counterUpdates;
  }
  public function getCounterUpdates()
  {
    return $this->counterUpdates;
  }
  public function setDynamicSourceSplit(Google_Service_Dataflow_DynamicSourceSplit $dynamicSourceSplit)
  {
    $this->dynamicSourceSplit = $dynamicSourceSplit;
  }
  public function getDynamicSourceSplit()
  {
    return $this->dynamicSourceSplit;
  }
  public function setErrors($errors)
  {
    $this->errors = $errors;
  }
  public function getErrors()
  {
    return $this->errors;
  }
  public function setMetricUpdates($metricUpdates)
  {
    $this->metricUpdates = $metricUpdates;
  }
  public function getMetricUpdates()
  {
    return $this->metricUpdates;
  }
  public function setProgress(Google_Service_Dataflow_ApproximateProgress $progress)
  {
    $this->progress = $progress;
  }
  public function getProgress()
  {
    return $this->progress;
  }
  public function setReportIndex($reportIndex)
  {
    $this->reportIndex = $reportIndex;
  }
  public function getReportIndex()
  {
    return $this->reportIndex;
  }
  public function setReportedProgress(Google_Service_Dataflow_ApproximateReportedProgress $reportedProgress)
  {
    $this->reportedProgress = $reportedProgress;
  }
  public function getReportedProgress()
  {
    return $this->reportedProgress;
  }
  public function setRequestedLeaseDuration($requestedLeaseDuration)
  {
    $this->requestedLeaseDuration = $requestedLeaseDuration;
  }
  public function getRequestedLeaseDuration()
  {
    return $this->requestedLeaseDuration;
  }
  public function setSourceFork(Google_Service_Dataflow_SourceFork $sourceFork)
  {
    $this->sourceFork = $sourceFork;
  }
  public function getSourceFork()
  {
    return $this->sourceFork;
  }
  public function setSourceOperationResponse(Google_Service_Dataflow_SourceOperationResponse $sourceOperationResponse)
  {
    $this->sourceOperationResponse = $sourceOperationResponse;
  }
  public function getSourceOperationResponse()
  {
    return $this->sourceOperationResponse;
  }
  public function setStopPosition(Google_Service_Dataflow_Position $stopPosition)
  {
    $this->stopPosition = $stopPosition;
  }
  public function getStopPosition()
  {
    return $this->stopPosition;
  }
  public function setWorkItemId($workItemId)
  {
    $this->workItemId = $workItemId;
  }
  public function getWorkItemId()
  {
    return $this->workItemId;
  }
}
