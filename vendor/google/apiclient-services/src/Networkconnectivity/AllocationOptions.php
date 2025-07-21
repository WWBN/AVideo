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

class AllocationOptions extends \Google\Model
{
  /**
   * @var string
   */
  public $allocationStrategy;
  /**
   * @var int
   */
  public $firstAvailableRangesLookupSize;

  /**
   * @param string
   */
  public function setAllocationStrategy($allocationStrategy)
  {
    $this->allocationStrategy = $allocationStrategy;
  }
  /**
   * @return string
   */
  public function getAllocationStrategy()
  {
    return $this->allocationStrategy;
  }
  /**
   * @param int
   */
  public function setFirstAvailableRangesLookupSize($firstAvailableRangesLookupSize)
  {
    $this->firstAvailableRangesLookupSize = $firstAvailableRangesLookupSize;
  }
  /**
   * @return int
   */
  public function getFirstAvailableRangesLookupSize()
  {
    return $this->firstAvailableRangesLookupSize;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AllocationOptions::class, 'Google_Service_Networkconnectivity_AllocationOptions');
