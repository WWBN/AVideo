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

namespace Google\Service\GKEHub;

class ConfigManagementContainerOverride extends \Google\Model
{
  /**
   * @var string
   */
  public $containerName;
  /**
   * @var string
   */
  public $cpuLimit;
  /**
   * @var string
   */
  public $cpuRequest;
  /**
   * @var string
   */
  public $memoryLimit;
  /**
   * @var string
   */
  public $memoryRequest;

  /**
   * @param string
   */
  public function setContainerName($containerName)
  {
    $this->containerName = $containerName;
  }
  /**
   * @return string
   */
  public function getContainerName()
  {
    return $this->containerName;
  }
  /**
   * @param string
   */
  public function setCpuLimit($cpuLimit)
  {
    $this->cpuLimit = $cpuLimit;
  }
  /**
   * @return string
   */
  public function getCpuLimit()
  {
    return $this->cpuLimit;
  }
  /**
   * @param string
   */
  public function setCpuRequest($cpuRequest)
  {
    $this->cpuRequest = $cpuRequest;
  }
  /**
   * @return string
   */
  public function getCpuRequest()
  {
    return $this->cpuRequest;
  }
  /**
   * @param string
   */
  public function setMemoryLimit($memoryLimit)
  {
    $this->memoryLimit = $memoryLimit;
  }
  /**
   * @return string
   */
  public function getMemoryLimit()
  {
    return $this->memoryLimit;
  }
  /**
   * @param string
   */
  public function setMemoryRequest($memoryRequest)
  {
    $this->memoryRequest = $memoryRequest;
  }
  /**
   * @return string
   */
  public function getMemoryRequest()
  {
    return $this->memoryRequest;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ConfigManagementContainerOverride::class, 'Google_Service_GKEHub_ConfigManagementContainerOverride');
