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

namespace Google\Service\Dataflow;

class Stack extends \Google\Model
{
  /**
   * @var string
   */
  public $stackContent;
  /**
   * @var int
   */
  public $threadCount;
  /**
   * @var string
   */
  public $threadName;
  /**
   * @var string
   */
  public $threadState;
  /**
   * @var string
   */
  public $timestamp;

  /**
   * @param string
   */
  public function setStackContent($stackContent)
  {
    $this->stackContent = $stackContent;
  }
  /**
   * @return string
   */
  public function getStackContent()
  {
    return $this->stackContent;
  }
  /**
   * @param int
   */
  public function setThreadCount($threadCount)
  {
    $this->threadCount = $threadCount;
  }
  /**
   * @return int
   */
  public function getThreadCount()
  {
    return $this->threadCount;
  }
  /**
   * @param string
   */
  public function setThreadName($threadName)
  {
    $this->threadName = $threadName;
  }
  /**
   * @return string
   */
  public function getThreadName()
  {
    return $this->threadName;
  }
  /**
   * @param string
   */
  public function setThreadState($threadState)
  {
    $this->threadState = $threadState;
  }
  /**
   * @return string
   */
  public function getThreadState()
  {
    return $this->threadState;
  }
  /**
   * @param string
   */
  public function setTimestamp($timestamp)
  {
    $this->timestamp = $timestamp;
  }
  /**
   * @return string
   */
  public function getTimestamp()
  {
    return $this->timestamp;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Stack::class, 'Google_Service_Dataflow_Stack');
