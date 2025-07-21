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

class ReservationBlock extends \Google\Model
{
  /**
   * @var int
   */
  public $count;
  /**
   * @var string
   */
  public $creationTimestamp;
  /**
   * @var string
   */
  public $id;
  /**
   * @var int
   */
  public $inUseCount;
  /**
   * @var string
   */
  public $kind;
  /**
   * @var string
   */
  public $name;
  protected $physicalTopologyType = ReservationBlockPhysicalTopology::class;
  protected $physicalTopologyDataType = '';
  protected $reservationMaintenanceType = GroupMaintenanceInfo::class;
  protected $reservationMaintenanceDataType = '';
  /**
   * @var int
   */
  public $reservationSubBlockCount;
  /**
   * @var int
   */
  public $reservationSubBlockInUseCount;
  /**
   * @var string
   */
  public $selfLink;
  /**
   * @var string
   */
  public $selfLinkWithId;
  /**
   * @var string
   */
  public $status;
  /**
   * @var string
   */
  public $zone;

  /**
   * @param int
   */
  public function setCount($count)
  {
    $this->count = $count;
  }
  /**
   * @return int
   */
  public function getCount()
  {
    return $this->count;
  }
  /**
   * @param string
   */
  public function setCreationTimestamp($creationTimestamp)
  {
    $this->creationTimestamp = $creationTimestamp;
  }
  /**
   * @return string
   */
  public function getCreationTimestamp()
  {
    return $this->creationTimestamp;
  }
  /**
   * @param string
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }
  /**
   * @param int
   */
  public function setInUseCount($inUseCount)
  {
    $this->inUseCount = $inUseCount;
  }
  /**
   * @return int
   */
  public function getInUseCount()
  {
    return $this->inUseCount;
  }
  /**
   * @param string
   */
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  /**
   * @return string
   */
  public function getKind()
  {
    return $this->kind;
  }
  /**
   * @param string
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param ReservationBlockPhysicalTopology
   */
  public function setPhysicalTopology(ReservationBlockPhysicalTopology $physicalTopology)
  {
    $this->physicalTopology = $physicalTopology;
  }
  /**
   * @return ReservationBlockPhysicalTopology
   */
  public function getPhysicalTopology()
  {
    return $this->physicalTopology;
  }
  /**
   * @param GroupMaintenanceInfo
   */
  public function setReservationMaintenance(GroupMaintenanceInfo $reservationMaintenance)
  {
    $this->reservationMaintenance = $reservationMaintenance;
  }
  /**
   * @return GroupMaintenanceInfo
   */
  public function getReservationMaintenance()
  {
    return $this->reservationMaintenance;
  }
  /**
   * @param int
   */
  public function setReservationSubBlockCount($reservationSubBlockCount)
  {
    $this->reservationSubBlockCount = $reservationSubBlockCount;
  }
  /**
   * @return int
   */
  public function getReservationSubBlockCount()
  {
    return $this->reservationSubBlockCount;
  }
  /**
   * @param int
   */
  public function setReservationSubBlockInUseCount($reservationSubBlockInUseCount)
  {
    $this->reservationSubBlockInUseCount = $reservationSubBlockInUseCount;
  }
  /**
   * @return int
   */
  public function getReservationSubBlockInUseCount()
  {
    return $this->reservationSubBlockInUseCount;
  }
  /**
   * @param string
   */
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  /**
   * @return string
   */
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  /**
   * @param string
   */
  public function setSelfLinkWithId($selfLinkWithId)
  {
    $this->selfLinkWithId = $selfLinkWithId;
  }
  /**
   * @return string
   */
  public function getSelfLinkWithId()
  {
    return $this->selfLinkWithId;
  }
  /**
   * @param string
   */
  public function setStatus($status)
  {
    $this->status = $status;
  }
  /**
   * @return string
   */
  public function getStatus()
  {
    return $this->status;
  }
  /**
   * @param string
   */
  public function setZone($zone)
  {
    $this->zone = $zone;
  }
  /**
   * @return string
   */
  public function getZone()
  {
    return $this->zone;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ReservationBlock::class, 'Google_Service_Compute_ReservationBlock');
