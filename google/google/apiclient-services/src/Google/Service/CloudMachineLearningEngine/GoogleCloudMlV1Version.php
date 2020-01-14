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

class Google_Service_CloudMachineLearningEngine_GoogleCloudMlV1Version extends Google_Model
{
  public $createTime;
  public $deploymentUri;
  public $description;
  public $isDefault;
  public $lastUseTime;
  protected $manualScalingType = 'Google_Service_CloudMachineLearningEngine_GoogleCloudMlV1ManualScaling';
  protected $manualScalingDataType = '';
  public $name;
  public $runtimeVersion;

  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  public function getCreateTime()
  {
    return $this->createTime;
  }
  public function setDeploymentUri($deploymentUri)
  {
    $this->deploymentUri = $deploymentUri;
  }
  public function getDeploymentUri()
  {
    return $this->deploymentUri;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setIsDefault($isDefault)
  {
    $this->isDefault = $isDefault;
  }
  public function getIsDefault()
  {
    return $this->isDefault;
  }
  public function setLastUseTime($lastUseTime)
  {
    $this->lastUseTime = $lastUseTime;
  }
  public function getLastUseTime()
  {
    return $this->lastUseTime;
  }
  public function setManualScaling(Google_Service_CloudMachineLearningEngine_GoogleCloudMlV1ManualScaling $manualScaling)
  {
    $this->manualScaling = $manualScaling;
  }
  public function getManualScaling()
  {
    return $this->manualScaling;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setRuntimeVersion($runtimeVersion)
  {
    $this->runtimeVersion = $runtimeVersion;
  }
  public function getRuntimeVersion()
  {
    return $this->runtimeVersion;
  }
}
