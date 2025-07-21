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

namespace Google\Service\WorkloadManager;

class BackupProperties extends \Google\Model
{
  /**
   * @var string
   */
  public $latestBackupStatus;
  /**
   * @var string
   */
  public $latestBackupTime;

  /**
   * @param string
   */
  public function setLatestBackupStatus($latestBackupStatus)
  {
    $this->latestBackupStatus = $latestBackupStatus;
  }
  /**
   * @return string
   */
  public function getLatestBackupStatus()
  {
    return $this->latestBackupStatus;
  }
  /**
   * @param string
   */
  public function setLatestBackupTime($latestBackupTime)
  {
    $this->latestBackupTime = $latestBackupTime;
  }
  /**
   * @return string
   */
  public function getLatestBackupTime()
  {
    return $this->latestBackupTime;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(BackupProperties::class, 'Google_Service_WorkloadManager_BackupProperties');
