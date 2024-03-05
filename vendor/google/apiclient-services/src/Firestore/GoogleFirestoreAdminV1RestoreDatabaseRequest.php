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

namespace Google\Service\Firestore;

class GoogleFirestoreAdminV1RestoreDatabaseRequest extends \Google\Model
{
  /**
   * @var string
   */
  public $backup;
  /**
   * @var string
   */
  public $databaseId;
  protected $databaseSnapshotType = GoogleFirestoreAdminV1DatabaseSnapshot::class;
  protected $databaseSnapshotDataType = '';

  /**
   * @param string
   */
  public function setBackup($backup)
  {
    $this->backup = $backup;
  }
  /**
   * @return string
   */
  public function getBackup()
  {
    return $this->backup;
  }
  /**
   * @param string
   */
  public function setDatabaseId($databaseId)
  {
    $this->databaseId = $databaseId;
  }
  /**
   * @return string
   */
  public function getDatabaseId()
  {
    return $this->databaseId;
  }
  /**
   * @param GoogleFirestoreAdminV1DatabaseSnapshot
   */
  public function setDatabaseSnapshot(GoogleFirestoreAdminV1DatabaseSnapshot $databaseSnapshot)
  {
    $this->databaseSnapshot = $databaseSnapshot;
  }
  /**
   * @return GoogleFirestoreAdminV1DatabaseSnapshot
   */
  public function getDatabaseSnapshot()
  {
    return $this->databaseSnapshot;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleFirestoreAdminV1RestoreDatabaseRequest::class, 'Google_Service_Firestore_GoogleFirestoreAdminV1RestoreDatabaseRequest');
