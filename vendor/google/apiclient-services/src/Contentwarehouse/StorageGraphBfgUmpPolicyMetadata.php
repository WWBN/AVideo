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

class StorageGraphBfgUmpPolicyMetadata extends \Google\Model
{
  /**
   * @var string
   */
  public $availabilityEnds;
  /**
   * @var string
   */
  public $availabilityStarts;
  protected $regionsAllowedType = KeGovernanceTypedRegions::class;
  protected $regionsAllowedDataType = '';

  /**
   * @param string
   */
  public function setAvailabilityEnds($availabilityEnds)
  {
    $this->availabilityEnds = $availabilityEnds;
  }
  /**
   * @return string
   */
  public function getAvailabilityEnds()
  {
    return $this->availabilityEnds;
  }
  /**
   * @param string
   */
  public function setAvailabilityStarts($availabilityStarts)
  {
    $this->availabilityStarts = $availabilityStarts;
  }
  /**
   * @return string
   */
  public function getAvailabilityStarts()
  {
    return $this->availabilityStarts;
  }
  /**
   * @param KeGovernanceTypedRegions
   */
  public function setRegionsAllowed(KeGovernanceTypedRegions $regionsAllowed)
  {
    $this->regionsAllowed = $regionsAllowed;
  }
  /**
   * @return KeGovernanceTypedRegions
   */
  public function getRegionsAllowed()
  {
    return $this->regionsAllowed;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(StorageGraphBfgUmpPolicyMetadata::class, 'Google_Service_Contentwarehouse_StorageGraphBfgUmpPolicyMetadata');
