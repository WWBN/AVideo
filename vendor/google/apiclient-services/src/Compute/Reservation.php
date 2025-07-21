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

class Reservation extends \Google\Collection
{
  protected $collection_key = 'linkedCommitments';
  protected $aggregateReservationType = AllocationAggregateReservation::class;
  protected $aggregateReservationDataType = '';
  /**
   * @var string
   */
  public $commitment;
  /**
   * @var string
   */
  public $creationTimestamp;
  protected $deleteAfterDurationType = Duration::class;
  protected $deleteAfterDurationDataType = '';
  /**
   * @var string
   */
  public $deleteAtTime;
  /**
   * @var string
   */
  public $deploymentType;
  /**
   * @var string
   */
  public $description;
  /**
   * @var bool
   */
  public $enableEmergentMaintenance;
  /**
   * @var string
   */
  public $id;
  /**
   * @var string
   */
  public $kind;
  /**
   * @var string[]
   */
  public $linkedCommitments;
  /**
   * @var string
   */
  public $name;
  protected $reservationSharingPolicyType = AllocationReservationSharingPolicy::class;
  protected $reservationSharingPolicyDataType = '';
  /**
   * @var string[]
   */
  public $resourcePolicies;
  protected $resourceStatusType = AllocationResourceStatus::class;
  protected $resourceStatusDataType = '';
  /**
   * @var bool
   */
  public $satisfiesPzs;
  /**
   * @var string
   */
  public $selfLink;
  protected $shareSettingsType = ShareSettings::class;
  protected $shareSettingsDataType = '';
  protected $specificReservationType = AllocationSpecificSKUReservation::class;
  protected $specificReservationDataType = '';
  /**
   * @var bool
   */
  public $specificReservationRequired;
  /**
   * @var string
   */
  public $status;
  /**
   * @var string
   */
  public $zone;

  /**
   * @param AllocationAggregateReservation
   */
  public function setAggregateReservation(AllocationAggregateReservation $aggregateReservation)
  {
    $this->aggregateReservation = $aggregateReservation;
  }
  /**
   * @return AllocationAggregateReservation
   */
  public function getAggregateReservation()
  {
    return $this->aggregateReservation;
  }
  /**
   * @param string
   */
  public function setCommitment($commitment)
  {
    $this->commitment = $commitment;
  }
  /**
   * @return string
   */
  public function getCommitment()
  {
    return $this->commitment;
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
   * @param Duration
   */
  public function setDeleteAfterDuration(Duration $deleteAfterDuration)
  {
    $this->deleteAfterDuration = $deleteAfterDuration;
  }
  /**
   * @return Duration
   */
  public function getDeleteAfterDuration()
  {
    return $this->deleteAfterDuration;
  }
  /**
   * @param string
   */
  public function setDeleteAtTime($deleteAtTime)
  {
    $this->deleteAtTime = $deleteAtTime;
  }
  /**
   * @return string
   */
  public function getDeleteAtTime()
  {
    return $this->deleteAtTime;
  }
  /**
   * @param string
   */
  public function setDeploymentType($deploymentType)
  {
    $this->deploymentType = $deploymentType;
  }
  /**
   * @return string
   */
  public function getDeploymentType()
  {
    return $this->deploymentType;
  }
  /**
   * @param string
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }
  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }
  /**
   * @param bool
   */
  public function setEnableEmergentMaintenance($enableEmergentMaintenance)
  {
    $this->enableEmergentMaintenance = $enableEmergentMaintenance;
  }
  /**
   * @return bool
   */
  public function getEnableEmergentMaintenance()
  {
    return $this->enableEmergentMaintenance;
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
   * @param string[]
   */
  public function setLinkedCommitments($linkedCommitments)
  {
    $this->linkedCommitments = $linkedCommitments;
  }
  /**
   * @return string[]
   */
  public function getLinkedCommitments()
  {
    return $this->linkedCommitments;
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
   * @param AllocationReservationSharingPolicy
   */
  public function setReservationSharingPolicy(AllocationReservationSharingPolicy $reservationSharingPolicy)
  {
    $this->reservationSharingPolicy = $reservationSharingPolicy;
  }
  /**
   * @return AllocationReservationSharingPolicy
   */
  public function getReservationSharingPolicy()
  {
    return $this->reservationSharingPolicy;
  }
  /**
   * @param string[]
   */
  public function setResourcePolicies($resourcePolicies)
  {
    $this->resourcePolicies = $resourcePolicies;
  }
  /**
   * @return string[]
   */
  public function getResourcePolicies()
  {
    return $this->resourcePolicies;
  }
  /**
   * @param AllocationResourceStatus
   */
  public function setResourceStatus(AllocationResourceStatus $resourceStatus)
  {
    $this->resourceStatus = $resourceStatus;
  }
  /**
   * @return AllocationResourceStatus
   */
  public function getResourceStatus()
  {
    return $this->resourceStatus;
  }
  /**
   * @param bool
   */
  public function setSatisfiesPzs($satisfiesPzs)
  {
    $this->satisfiesPzs = $satisfiesPzs;
  }
  /**
   * @return bool
   */
  public function getSatisfiesPzs()
  {
    return $this->satisfiesPzs;
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
   * @param ShareSettings
   */
  public function setShareSettings(ShareSettings $shareSettings)
  {
    $this->shareSettings = $shareSettings;
  }
  /**
   * @return ShareSettings
   */
  public function getShareSettings()
  {
    return $this->shareSettings;
  }
  /**
   * @param AllocationSpecificSKUReservation
   */
  public function setSpecificReservation(AllocationSpecificSKUReservation $specificReservation)
  {
    $this->specificReservation = $specificReservation;
  }
  /**
   * @return AllocationSpecificSKUReservation
   */
  public function getSpecificReservation()
  {
    return $this->specificReservation;
  }
  /**
   * @param bool
   */
  public function setSpecificReservationRequired($specificReservationRequired)
  {
    $this->specificReservationRequired = $specificReservationRequired;
  }
  /**
   * @return bool
   */
  public function getSpecificReservationRequired()
  {
    return $this->specificReservationRequired;
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
class_alias(Reservation::class, 'Google_Service_Compute_Reservation');
