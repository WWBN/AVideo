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

class InterconnectGroupsOperationalStatusInterconnectStatus extends \Google\Model
{
  /**
   * @var bool
   */
  public $adminEnabled;
  protected $diagnosticsType = InterconnectDiagnostics::class;
  protected $diagnosticsDataType = '';
  /**
   * @var string
   */
  public $interconnect;
  /**
   * @var string
   */
  public $isActive;

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
   * @param InterconnectDiagnostics
   */
  public function setDiagnostics(InterconnectDiagnostics $diagnostics)
  {
    $this->diagnostics = $diagnostics;
  }
  /**
   * @return InterconnectDiagnostics
   */
  public function getDiagnostics()
  {
    return $this->diagnostics;
  }
  /**
   * @param string
   */
  public function setInterconnect($interconnect)
  {
    $this->interconnect = $interconnect;
  }
  /**
   * @return string
   */
  public function getInterconnect()
  {
    return $this->interconnect;
  }
  /**
   * @param string
   */
  public function setIsActive($isActive)
  {
    $this->isActive = $isActive;
  }
  /**
   * @return string
   */
  public function getIsActive()
  {
    return $this->isActive;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(InterconnectGroupsOperationalStatusInterconnectStatus::class, 'Google_Service_Compute_InterconnectGroupsOperationalStatusInterconnectStatus');
