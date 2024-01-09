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

namespace Google\Service\Networkconnectivity;

class ConsumerPscConfig extends \Google\Model
{
  /**
   * @var bool
   */
  public $disableGlobalAccess;
  /**
   * @var string
   */
  public $network;
  /**
   * @var string
   */
  public $project;

  /**
   * @param bool
   */
  public function setDisableGlobalAccess($disableGlobalAccess)
  {
    $this->disableGlobalAccess = $disableGlobalAccess;
  }
  /**
   * @return bool
   */
  public function getDisableGlobalAccess()
  {
    return $this->disableGlobalAccess;
  }
  /**
   * @param string
   */
  public function setNetwork($network)
  {
    $this->network = $network;
  }
  /**
   * @return string
   */
  public function getNetwork()
  {
    return $this->network;
  }
  /**
   * @param string
   */
  public function setProject($project)
  {
    $this->project = $project;
  }
  /**
   * @return string
   */
  public function getProject()
  {
    return $this->project;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ConsumerPscConfig::class, 'Google_Service_Networkconnectivity_ConsumerPscConfig');
