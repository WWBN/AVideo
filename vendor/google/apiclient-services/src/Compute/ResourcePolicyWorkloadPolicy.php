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

namespace Google\Service\Compute;

class ResourcePolicyWorkloadPolicy extends \Google\Model
{
  /**
   * @var string
   */
  public $acceleratorTopology;
  /**
   * @var string
   */
  public $maxTopologyDistance;
  /**
   * @var string
   */
  public $type;

  /**
   * @param string
   */
  public function setAcceleratorTopology($acceleratorTopology)
  {
    $this->acceleratorTopology = $acceleratorTopology;
  }
  /**
   * @return string
   */
  public function getAcceleratorTopology()
  {
    return $this->acceleratorTopology;
  }
  /**
   * @param string
   */
  public function setMaxTopologyDistance($maxTopologyDistance)
  {
    $this->maxTopologyDistance = $maxTopologyDistance;
  }
  /**
   * @return string
   */
  public function getMaxTopologyDistance()
  {
    return $this->maxTopologyDistance;
  }
  /**
   * @param string
   */
  public function setType($type)
  {
    $this->type = $type;
  }
  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ResourcePolicyWorkloadPolicy::class, 'Google_Service_Compute_ResourcePolicyWorkloadPolicy');
