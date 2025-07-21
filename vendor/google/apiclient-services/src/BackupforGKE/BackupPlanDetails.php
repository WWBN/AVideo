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

namespace Google\Service\BackupforGKE;

class BackupPlanDetails extends \Google\Model
{
  protected $backupConfigDetailsType = BackupConfigDetails::class;
  protected $backupConfigDetailsDataType = '';
  /**
   * @var string
   */
  public $lastSuccessfulBackup;
  /**
   * @var string
   */
  public $lastSuccessfulBackupTime;
  /**
   * @var string
   */
  public $nextScheduledBackupTime;
  /**
   * @var int
   */
  public $protectedPodCount;
  protected $retentionPolicyDetailsType = RetentionPolicyDetails::class;
  protected $retentionPolicyDetailsDataType = '';
  /**
   * @var int
   */
  public $rpoRiskLevel;
  /**
   * @var string
   */
  public $state;

  /**
   * @param BackupConfigDetails
   */
  public function setBackupConfigDetails(BackupConfigDetails $backupConfigDetails)
  {
    $this->backupConfigDetails = $backupConfigDetails;
  }
  /**
   * @return BackupConfigDetails
   */
  public function getBackupConfigDetails()
  {
    return $this->backupConfigDetails;
  }
  /**
   * @param string
   */
  public function setLastSuccessfulBackup($lastSuccessfulBackup)
  {
    $this->lastSuccessfulBackup = $lastSuccessfulBackup;
  }
  /**
   * @return string
   */
  public function getLastSuccessfulBackup()
  {
    return $this->lastSuccessfulBackup;
  }
  /**
   * @param string
   */
  public function setLastSuccessfulBackupTime($lastSuccessfulBackupTime)
  {
    $this->lastSuccessfulBackupTime = $lastSuccessfulBackupTime;
  }
  /**
   * @return string
   */
  public function getLastSuccessfulBackupTime()
  {
    return $this->lastSuccessfulBackupTime;
  }
  /**
   * @param string
   */
  public function setNextScheduledBackupTime($nextScheduledBackupTime)
  {
    $this->nextScheduledBackupTime = $nextScheduledBackupTime;
  }
  /**
   * @return string
   */
  public function getNextScheduledBackupTime()
  {
    return $this->nextScheduledBackupTime;
  }
  /**
   * @param int
   */
  public function setProtectedPodCount($protectedPodCount)
  {
    $this->protectedPodCount = $protectedPodCount;
  }
  /**
   * @return int
   */
  public function getProtectedPodCount()
  {
    return $this->protectedPodCount;
  }
  /**
   * @param RetentionPolicyDetails
   */
  public function setRetentionPolicyDetails(RetentionPolicyDetails $retentionPolicyDetails)
  {
    $this->retentionPolicyDetails = $retentionPolicyDetails;
  }
  /**
   * @return RetentionPolicyDetails
   */
  public function getRetentionPolicyDetails()
  {
    return $this->retentionPolicyDetails;
  }
  /**
   * @param int
   */
  public function setRpoRiskLevel($rpoRiskLevel)
  {
    $this->rpoRiskLevel = $rpoRiskLevel;
  }
  /**
   * @return int
   */
  public function getRpoRiskLevel()
  {
    return $this->rpoRiskLevel;
  }
  /**
   * @param string
   */
  public function setState($state)
  {
    $this->state = $state;
  }
  /**
   * @return string
   */
  public function getState()
  {
    return $this->state;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(BackupPlanDetails::class, 'Google_Service_BackupforGKE_BackupPlanDetails');
