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

class InterconnectGroupsCreateMembersInterconnectInput extends \Google\Collection
{
  protected $collection_key = 'requestedFeatures';
  /**
   * @var bool
   */
  public $adminEnabled;
  /**
   * @var string
   */
  public $customerName;
  /**
   * @var string
   */
  public $description;
  /**
   * @var string
   */
  public $facility;
  /**
   * @var string
   */
  public $interconnectType;
  /**
   * @var string
   */
  public $linkType;
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $nocContactEmail;
  /**
   * @var string
   */
  public $remoteLocation;
  /**
   * @var string[]
   */
  public $requestedFeatures;
  /**
   * @var int
   */
  public $requestedLinkCount;

  /**
   * @param bool
   */
  public function setAdminEnabled($adminEnabled)
  {
    $this->adminEnabled = $adminEnabled;
  }
  /**
   * @return bool
   */
  public function getAdminEnabled()
  {
    return $this->adminEnabled;
  }
  /**
   * @param string
   */
  public function setCustomerName($customerName)
  {
    $this->customerName = $customerName;
  }
  /**
   * @return string
   */
  public function getCustomerName()
  {
    return $this->customerName;
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
   * @param string
   */
  public function setFacility($facility)
  {
    $this->facility = $facility;
  }
  /**
   * @return string
   */
  public function getFacility()
  {
    return $this->facility;
  }
  /**
   * @param string
   */
  public function setInterconnectType($interconnectType)
  {
    $this->interconnectType = $interconnectType;
  }
  /**
   * @return string
   */
  public function getInterconnectType()
  {
    return $this->interconnectType;
  }
  /**
   * @param string
   */
  public function setLinkType($linkType)
  {
    $this->linkType = $linkType;
  }
  /**
   * @return string
   */
  public function getLinkType()
  {
    return $this->linkType;
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
   * @param string
   */
  public function setNocContactEmail($nocContactEmail)
  {
    $this->nocContactEmail = $nocContactEmail;
  }
  /**
   * @return string
   */
  public function getNocContactEmail()
  {
    return $this->nocContactEmail;
  }
  /**
   * @param string
   */
  public function setRemoteLocation($remoteLocation)
  {
    $this->remoteLocation = $remoteLocation;
  }
  /**
   * @return string
   */
  public function getRemoteLocation()
  {
    return $this->remoteLocation;
  }
  /**
   * @param string[]
   */
  public function setRequestedFeatures($requestedFeatures)
  {
    $this->requestedFeatures = $requestedFeatures;
  }
  /**
   * @return string[]
   */
  public function getRequestedFeatures()
  {
    return $this->requestedFeatures;
  }
  /**
   * @param int
   */
  public function setRequestedLinkCount($requestedLinkCount)
  {
    $this->requestedLinkCount = $requestedLinkCount;
  }
  /**
   * @return int
   */
  public function getRequestedLinkCount()
  {
    return $this->requestedLinkCount;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(InterconnectGroupsCreateMembersInterconnectInput::class, 'Google_Service_Compute_InterconnectGroupsCreateMembersInterconnectInput');
