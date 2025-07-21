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

class DatabaseProperties extends \Google\Model
{
  protected $backupPropertiesType = BackupProperties::class;
  protected $backupPropertiesDataType = '';
  /**
   * @var string
   */
  public $databaseType;

  /**
   * @param BackupProperties
   */
  public function setBackupProperties(BackupProperties $backupProperties)
  {
    $this->backupProperties = $backupProperties;
  }
  /**
   * @return BackupProperties
   */
  public function getBackupProperties()
  {
    return $this->backupProperties;
  }
  /**
   * @param string
   */
  public function setDatabaseType($databaseType)
  {
    $this->databaseType = $databaseType;
  }
  /**
   * @return string
   */
  public function getDatabaseType()
  {
    return $this->databaseType;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(DatabaseProperties::class, 'Google_Service_WorkloadManager_DatabaseProperties');
