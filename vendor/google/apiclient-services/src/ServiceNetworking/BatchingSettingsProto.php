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

namespace Google\Service\ServiceNetworking;

class BatchingSettingsProto extends \Google\Model
{
  /**
   * @var string
   */
  public $delayThreshold;
  /**
   * @var int
   */
  public $elementCountLimit;
  /**
   * @var int
   */
  public $elementCountThreshold;
  /**
   * @var int
   */
  public $flowControlByteLimit;
  /**
   * @var int
   */
  public $flowControlElementLimit;
  /**
   * @var string
   */
  public $flowControlLimitExceededBehavior;
  /**
   * @var int
   */
  public $requestByteLimit;
  /**
   * @var string
   */
  public $requestByteThreshold;

  /**
   * @param string
   */
  public function setDelayThreshold($delayThreshold)
  {
    $this->delayThreshold = $delayThreshold;
  }
  /**
   * @return string
   */
  public function getDelayThreshold()
  {
    return $this->delayThreshold;
  }
  /**
   * @param int
   */
  public function setElementCountLimit($elementCountLimit)
  {
    $this->elementCountLimit = $elementCountLimit;
  }
  /**
   * @return int
   */
  public function getElementCountLimit()
  {
    return $this->elementCountLimit;
  }
  /**
   * @param int
   */
  public function setElementCountThreshold($elementCountThreshold)
  {
    $this->elementCountThreshold = $elementCountThreshold;
  }
  /**
   * @return int
   */
  public function getElementCountThreshold()
  {
    return $this->elementCountThreshold;
  }
  /**
   * @param int
   */
  public function setFlowControlByteLimit($flowControlByteLimit)
  {
    $this->flowControlByteLimit = $flowControlByteLimit;
  }
  /**
   * @return int
   */
  public function getFlowControlByteLimit()
  {
    return $this->flowControlByteLimit;
  }
  /**
   * @param int
   */
  public function setFlowControlElementLimit($flowControlElementLimit)
  {
    $this->flowControlElementLimit = $flowControlElementLimit;
  }
  /**
   * @return int
   */
  public function getFlowControlElementLimit()
  {
    return $this->flowControlElementLimit;
  }
  /**
   * @param string
   */
  public function setFlowControlLimitExceededBehavior($flowControlLimitExceededBehavior)
  {
    $this->flowControlLimitExceededBehavior = $flowControlLimitExceededBehavior;
  }
  /**
   * @return string
   */
  public function getFlowControlLimitExceededBehavior()
  {
    return $this->flowControlLimitExceededBehavior;
  }
  /**
   * @param int
   */
  public function setRequestByteLimit($requestByteLimit)
  {
    $this->requestByteLimit = $requestByteLimit;
  }
  /**
   * @return int
   */
  public function getRequestByteLimit()
  {
    return $this->requestByteLimit;
  }
  /**
   * @param string
   */
  public function setRequestByteThreshold($requestByteThreshold)
  {
    $this->requestByteThreshold = $requestByteThreshold;
  }
  /**
   * @return string
   */
  public function getRequestByteThreshold()
  {
    return $this->requestByteThreshold;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(BatchingSettingsProto::class, 'Google_Service_ServiceNetworking_BatchingSettingsProto');
