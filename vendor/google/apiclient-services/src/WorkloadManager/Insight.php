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

namespace Google\Service\WorkloadManager;

class Insight extends \Google\Model
{
  protected $sapDiscoveryType = SapDiscovery::class;
  protected $sapDiscoveryDataType = '';
  public $sapDiscovery;
  protected $sapValidationType = SapValidation::class;
  protected $sapValidationDataType = '';
  public $sapValidation;
  /**
   * @var string
   */
  public $sentTime;

  /**
   * @param SapDiscovery
   */
  public function setSapDiscovery(SapDiscovery $sapDiscovery)
  {
    $this->sapDiscovery = $sapDiscovery;
  }
  /**
   * @return SapDiscovery
   */
  public function getSapDiscovery()
  {
    return $this->sapDiscovery;
  }
  /**
   * @param SapValidation
   */
  public function setSapValidation(SapValidation $sapValidation)
  {
    $this->sapValidation = $sapValidation;
  }
  /**
   * @return SapValidation
   */
  public function getSapValidation()
  {
    return $this->sapValidation;
  }
  /**
   * @param string
   */
  public function setSentTime($sentTime)
  {
    $this->sentTime = $sentTime;
  }
  /**
   * @return string
   */
  public function getSentTime()
  {
    return $this->sentTime;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Insight::class, 'Google_Service_WorkloadManager_Insight');
