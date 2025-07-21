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

namespace Google\Service\ManagedKafka;

class CapacityConfig extends \Google\Model
{
  /**
   * @var string
   */
  public $memoryBytes;
  /**
   * @var string
   */
  public $vcpuCount;

  /**
   * @param string
   */
  public function setMemoryBytes($memoryBytes)
  {
    $this->memoryBytes = $memoryBytes;
  }
  /**
   * @return string
   */
  public function getMemoryBytes()
  {
    return $this->memoryBytes;
  }
  /**
   * @param string
   */
  public function setVcpuCount($vcpuCount)
  {
    $this->vcpuCount = $vcpuCount;
  }
  /**
   * @return string
   */
  public function getVcpuCount()
  {
    return $this->vcpuCount;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CapacityConfig::class, 'Google_Service_ManagedKafka_CapacityConfig');
