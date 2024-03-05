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

namespace Google\Service\Contentwarehouse;

class AssistantPfrSmartHomeIntentMetadata extends \Google\Collection
{
  protected $collection_key = 'deviceRdMetadata';
  protected $deviceRdMetadataType = AssistantPfrDeviceRdMetadata::class;
  protected $deviceRdMetadataDataType = 'array';
  /**
   * @var string
   */
  public $intentName;
  /**
   * @var bool
   */
  public $isExactMatch;
  /**
   * @var bool
   */
  public $isGrounded;

  /**
   * @param AssistantPfrDeviceRdMetadata[]
   */
  public function setDeviceRdMetadata($deviceRdMetadata)
  {
    $this->deviceRdMetadata = $deviceRdMetadata;
  }
  /**
   * @return AssistantPfrDeviceRdMetadata[]
   */
  public function getDeviceRdMetadata()
  {
    return $this->deviceRdMetadata;
  }
  /**
   * @param string
   */
  public function setIntentName($intentName)
  {
    $this->intentName = $intentName;
  }
  /**
   * @return string
   */
  public function getIntentName()
  {
    return $this->intentName;
  }
  /**
   * @param bool
   */
  public function setIsExactMatch($isExactMatch)
  {
    $this->isExactMatch = $isExactMatch;
  }
  /**
   * @return bool
   */
  public function getIsExactMatch()
  {
    return $this->isExactMatch;
  }
  /**
   * @param bool
   */
  public function setIsGrounded($isGrounded)
  {
    $this->isGrounded = $isGrounded;
  }
  /**
   * @return bool
   */
  public function getIsGrounded()
  {
    return $this->isGrounded;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantPfrSmartHomeIntentMetadata::class, 'Google_Service_Contentwarehouse_AssistantPfrSmartHomeIntentMetadata');
