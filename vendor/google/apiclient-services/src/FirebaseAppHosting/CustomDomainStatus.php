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

namespace Google\Service\FirebaseAppHosting;

class CustomDomainStatus extends \Google\Collection
{
  protected $collection_key = 'requiredDnsUpdates';
  /**
   * @var string
   */
  public $certState;
  /**
   * @var string
   */
  public $hostState;
  protected $issuesType = Status::class;
  protected $issuesDataType = 'array';
  /**
   * @var string
   */
  public $ownershipState;
  protected $requiredDnsUpdatesType = DnsUpdates::class;
  protected $requiredDnsUpdatesDataType = 'array';

  /**
   * @param string
   */
  public function setCertState($certState)
  {
    $this->certState = $certState;
  }
  /**
   * @return string
   */
  public function getCertState()
  {
    return $this->certState;
  }
  /**
   * @param string
   */
  public function setHostState($hostState)
  {
    $this->hostState = $hostState;
  }
  /**
   * @return string
   */
  public function getHostState()
  {
    return $this->hostState;
  }
  /**
   * @param Status[]
   */
  public function setIssues($issues)
  {
    $this->issues = $issues;
  }
  /**
   * @return Status[]
   */
  public function getIssues()
  {
    return $this->issues;
  }
  /**
   * @param string
   */
  public function setOwnershipState($ownershipState)
  {
    $this->ownershipState = $ownershipState;
  }
  /**
   * @return string
   */
  public function getOwnershipState()
  {
    return $this->ownershipState;
  }
  /**
   * @param DnsUpdates[]
   */
  public function setRequiredDnsUpdates($requiredDnsUpdates)
  {
    $this->requiredDnsUpdates = $requiredDnsUpdates;
  }
  /**
   * @return DnsUpdates[]
   */
  public function getRequiredDnsUpdates()
  {
    return $this->requiredDnsUpdates;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CustomDomainStatus::class, 'Google_Service_FirebaseAppHosting_CustomDomainStatus');
