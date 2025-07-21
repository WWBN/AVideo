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

class InterconnectGroupsOperationalStatus extends \Google\Collection
{
  protected $collection_key = 'interconnectStatuses';
  protected $configuredType = InterconnectGroupConfigured::class;
  protected $configuredDataType = '';
  /**
   * @var string
   */
  public $groupStatus;
  protected $intentType = InterconnectGroupIntent::class;
  protected $intentDataType = '';
  protected $interconnectStatusesType = InterconnectGroupsOperationalStatusInterconnectStatus::class;
  protected $interconnectStatusesDataType = 'array';
  protected $operationalType = InterconnectGroupConfigured::class;
  protected $operationalDataType = '';

  /**
   * @param InterconnectGroupConfigured
   */
  public function setConfigured(InterconnectGroupConfigured $configured)
  {
    $this->configured = $configured;
  }
  /**
   * @return InterconnectGroupConfigured
   */
  public function getConfigured()
  {
    return $this->configured;
  }
  /**
   * @param string
   */
  public function setGroupStatus($groupStatus)
  {
    $this->groupStatus = $groupStatus;
  }
  /**
   * @return string
   */
  public function getGroupStatus()
  {
    return $this->groupStatus;
  }
  /**
   * @param InterconnectGroupIntent
   */
  public function setIntent(InterconnectGroupIntent $intent)
  {
    $this->intent = $intent;
  }
  /**
   * @return InterconnectGroupIntent
   */
  public function getIntent()
  {
    return $this->intent;
  }
  /**
   * @param InterconnectGroupsOperationalStatusInterconnectStatus[]
   */
  public function setInterconnectStatuses($interconnectStatuses)
  {
    $this->interconnectStatuses = $interconnectStatuses;
  }
  /**
   * @return InterconnectGroupsOperationalStatusInterconnectStatus[]
   */
  public function getInterconnectStatuses()
  {
    return $this->interconnectStatuses;
  }
  /**
   * @param InterconnectGroupConfigured
   */
  public function setOperational(InterconnectGroupConfigured $operational)
  {
    $this->operational = $operational;
  }
  /**
   * @return InterconnectGroupConfigured
   */
  public function getOperational()
  {
    return $this->operational;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(InterconnectGroupsOperationalStatus::class, 'Google_Service_Compute_InterconnectGroupsOperationalStatus');
