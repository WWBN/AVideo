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

namespace Google\Service\FirebaseAppHosting;

class RunConfig extends \Google\Model
{
  /**
   * @var int
   */
  public $concurrency;
  /**
   * @var float
   */
  public $cpu;
  /**
   * @var int
   */
  public $maxInstances;
  /**
   * @var int
   */
  public $memoryMib;
  /**
   * @var int
   */
  public $minInstances;

  /**
   * @param int
   */
  public function setConcurrency($concurrency)
  {
    $this->concurrency = $concurrency;
  }
  /**
   * @return int
   */
  public function getConcurrency()
  {
    return $this->concurrency;
  }
  /**
   * @param float
   */
  public function setCpu($cpu)
  {
    $this->cpu = $cpu;
  }
  /**
   * @return float
   */
  public function getCpu()
  {
    return $this->cpu;
  }
  /**
   * @param int
   */
  public function setMaxInstances($maxInstances)
  {
    $this->maxInstances = $maxInstances;
  }
  /**
   * @return int
   */
  public function getMaxInstances()
  {
    return $this->maxInstances;
  }
  /**
   * @param int
   */
  public function setMemoryMib($memoryMib)
  {
    $this->memoryMib = $memoryMib;
  }
  /**
   * @return int
   */
  public function getMemoryMib()
  {
    return $this->memoryMib;
  }
  /**
   * @param int
   */
  public function setMinInstances($minInstances)
  {
    $this->minInstances = $minInstances;
  }
  /**
   * @return int
   */
  public function getMinInstances()
  {
    return $this->minInstances;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(RunConfig::class, 'Google_Service_FirebaseAppHosting_RunConfig');
