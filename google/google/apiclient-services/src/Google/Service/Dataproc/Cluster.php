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

class Google_Service_Dataproc_Cluster extends Google_Collection
{
  protected $collection_key = 'statusHistory';
  public $clusterName;
  public $clusterUuid;
  protected $configType = 'Google_Service_Dataproc_ClusterConfig';
  protected $configDataType = '';
  public $labels;
  protected $metricsType = 'Google_Service_Dataproc_ClusterMetrics';
  protected $metricsDataType = '';
  public $projectId;
  protected $statusType = 'Google_Service_Dataproc_ClusterStatus';
  protected $statusDataType = '';
  protected $statusHistoryType = 'Google_Service_Dataproc_ClusterStatus';
  protected $statusHistoryDataType = 'array';

  public function setClusterName($clusterName)
  {
    $this->clusterName = $clusterName;
  }
  public function getClusterName()
  {
    return $this->clusterName;
  }
  public function setClusterUuid($clusterUuid)
  {
    $this->clusterUuid = $clusterUuid;
  }
  public function getClusterUuid()
  {
    return $this->clusterUuid;
  }
  public function setConfig(Google_Service_Dataproc_ClusterConfig $config)
  {
    $this->config = $config;
  }
  public function getConfig()
  {
    return $this->config;
  }
  public function setLabels($labels)
  {
    $this->labels = $labels;
  }
  public function getLabels()
  {
    return $this->labels;
  }
  public function setMetrics(Google_Service_Dataproc_ClusterMetrics $metrics)
  {
    $this->metrics = $metrics;
  }
  public function getMetrics()
  {
    return $this->metrics;
  }
  public function setProjectId($projectId)
  {
    $this->projectId = $projectId;
  }
  public function getProjectId()
  {
    return $this->projectId;
  }
  public function setStatus(Google_Service_Dataproc_ClusterStatus $status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setStatusHistory($statusHistory)
  {
    $this->statusHistory = $statusHistory;
  }
  public function getStatusHistory()
  {
    return $this->statusHistory;
  }
}
