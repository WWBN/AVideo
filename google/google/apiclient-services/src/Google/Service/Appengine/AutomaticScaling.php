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

class Google_Service_Appengine_AutomaticScaling extends Google_Model
{
  public $coolDownPeriod;
  protected $cpuUtilizationType = 'Google_Service_Appengine_CpuUtilization';
  protected $cpuUtilizationDataType = '';
  protected $diskUtilizationType = 'Google_Service_Appengine_DiskUtilization';
  protected $diskUtilizationDataType = '';
  public $maxConcurrentRequests;
  public $maxIdleInstances;
  public $maxPendingLatency;
  public $maxTotalInstances;
  public $minIdleInstances;
  public $minPendingLatency;
  public $minTotalInstances;
  protected $networkUtilizationType = 'Google_Service_Appengine_NetworkUtilization';
  protected $networkUtilizationDataType = '';
  protected $requestUtilizationType = 'Google_Service_Appengine_RequestUtilization';
  protected $requestUtilizationDataType = '';

  public function setCoolDownPeriod($coolDownPeriod)
  {
    $this->coolDownPeriod = $coolDownPeriod;
  }
  public function getCoolDownPeriod()
  {
    return $this->coolDownPeriod;
  }
  public function setCpuUtilization(Google_Service_Appengine_CpuUtilization $cpuUtilization)
  {
    $this->cpuUtilization = $cpuUtilization;
  }
  public function getCpuUtilization()
  {
    return $this->cpuUtilization;
  }
  public function setDiskUtilization(Google_Service_Appengine_DiskUtilization $diskUtilization)
  {
    $this->diskUtilization = $diskUtilization;
  }
  public function getDiskUtilization()
  {
    return $this->diskUtilization;
  }
  public function setMaxConcurrentRequests($maxConcurrentRequests)
  {
    $this->maxConcurrentRequests = $maxConcurrentRequests;
  }
  public function getMaxConcurrentRequests()
  {
    return $this->maxConcurrentRequests;
  }
  public function setMaxIdleInstances($maxIdleInstances)
  {
    $this->maxIdleInstances = $maxIdleInstances;
  }
  public function getMaxIdleInstances()
  {
    return $this->maxIdleInstances;
  }
  public function setMaxPendingLatency($maxPendingLatency)
  {
    $this->maxPendingLatency = $maxPendingLatency;
  }
  public function getMaxPendingLatency()
  {
    return $this->maxPendingLatency;
  }
  public function setMaxTotalInstances($maxTotalInstances)
  {
    $this->maxTotalInstances = $maxTotalInstances;
  }
  public function getMaxTotalInstances()
  {
    return $this->maxTotalInstances;
  }
  public function setMinIdleInstances($minIdleInstances)
  {
    $this->minIdleInstances = $minIdleInstances;
  }
  public function getMinIdleInstances()
  {
    return $this->minIdleInstances;
  }
  public function setMinPendingLatency($minPendingLatency)
  {
    $this->minPendingLatency = $minPendingLatency;
  }
  public function getMinPendingLatency()
  {
    return $this->minPendingLatency;
  }
  public function setMinTotalInstances($minTotalInstances)
  {
    $this->minTotalInstances = $minTotalInstances;
  }
  public function getMinTotalInstances()
  {
    return $this->minTotalInstances;
  }
  public function setNetworkUtilization(Google_Service_Appengine_NetworkUtilization $networkUtilization)
  {
    $this->networkUtilization = $networkUtilization;
  }
  public function getNetworkUtilization()
  {
    return $this->networkUtilization;
  }
  public function setRequestUtilization(Google_Service_Appengine_RequestUtilization $requestUtilization)
  {
    $this->requestUtilization = $requestUtilization;
  }
  public function getRequestUtilization()
  {
    return $this->requestUtilization;
  }
}
