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

namespace Google\Service\Reports;

class ActivityActorApplicationInfo extends \Google\Model
{
  /**
   * @var string
   */
  public $applicationName;
  /**
   * @var bool
   */
  public $impersonation;
  /**
   * @var string
   */
  public $oauthClientId;

  /**
   * @param string
   */
  public function setApplicationName($applicationName)
  {
    $this->applicationName = $applicationName;
  }
  /**
   * @return string
   */
  public function getApplicationName()
  {
    return $this->applicationName;
  }
  /**
   * @param bool
   */
  public function setImpersonation($impersonation)
  {
    $this->impersonation = $impersonation;
  }
  /**
   * @return bool
   */
  public function getImpersonation()
  {
    return $this->impersonation;
  }
  /**
   * @param string
   */
  public function setOauthClientId($oauthClientId)
  {
    $this->oauthClientId = $oauthClientId;
  }
  /**
   * @return string
   */
  public function getOauthClientId()
  {
    return $this->oauthClientId;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ActivityActorApplicationInfo::class, 'Google_Service_Reports_ActivityActorApplicationInfo');
