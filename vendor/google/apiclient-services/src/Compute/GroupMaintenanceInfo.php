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

class GroupMaintenanceInfo extends \Google\Model
{
  /**
   * @var int
   */
  public $maintenanceOngoingCount;
  /**
   * @var int
   */
  public $maintenancePendingCount;
  /**
   * @var string
   */
  public $schedulingType;
  protected $upcomingGroupMaintenanceType = UpcomingMaintenance::class;
  protected $upcomingGroupMaintenanceDataType = '';

  /**
   * @param int
   */
  public function setMaintenanceOngoingCount($maintenanceOngoingCount)
  {
    $this->maintenanceOngoingCount = $maintenanceOngoingCount;
  }
  /**
   * @return int
   */
  public function getMaintenanceOngoingCount()
  {
    return $this->maintenanceOngoingCount;
  }
  /**
   * @param int
   */
  public function setMaintenancePendingCount($maintenancePendingCount)
  {
    $this->maintenancePendingCount = $maintenancePendingCount;
  }
  /**
   * @return int
   */
  public function getMaintenancePendingCount()
  {
    return $this->maintenancePendingCount;
  }
  /**
   * @param string
   */
  public function setSchedulingType($schedulingType)
  {
    $this->schedulingType = $schedulingType;
  }
  /**
   * @return string
   */
  public function getSchedulingType()
  {
    return $this->schedulingType;
  }
  /**
   * @param UpcomingMaintenance
   */
  public function setUpcomingGroupMaintenance(UpcomingMaintenance $upcomingGroupMaintenance)
  {
    $this->upcomingGroupMaintenance = $upcomingGroupMaintenance;
  }
  /**
   * @return UpcomingMaintenance
   */
  public function getUpcomingGroupMaintenance()
  {
    return $this->upcomingGroupMaintenance;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GroupMaintenanceInfo::class, 'Google_Service_Compute_GroupMaintenanceInfo');
