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

namespace Google\Service\CloudDataplex;

class GoogleCloudDataplexV1DataDiscoveryResultScanStatistics extends \Google\Model
{
  /**
   * @var string
   */
  public $dataProcessedBytes;
  /**
   * @var int
   */
  public $filesExcluded;
  /**
   * @var int
   */
  public $filesetsCreated;
  /**
   * @var int
   */
  public $filesetsDeleted;
  /**
   * @var int
   */
  public $filesetsUpdated;
  /**
   * @var int
   */
  public $scannedFileCount;
  /**
   * @var int
   */
  public $tablesCreated;
  /**
   * @var int
   */
  public $tablesDeleted;
  /**
   * @var int
   */
  public $tablesUpdated;

  /**
   * @param string
   */
  public function setDataProcessedBytes($dataProcessedBytes)
  {
    $this->dataProcessedBytes = $dataProcessedBytes;
  }
  /**
   * @return string
   */
  public function getDataProcessedBytes()
  {
    return $this->dataProcessedBytes;
  }
  /**
   * @param int
   */
  public function setFilesExcluded($filesExcluded)
  {
    $this->filesExcluded = $filesExcluded;
  }
  /**
   * @return int
   */
  public function getFilesExcluded()
  {
    return $this->filesExcluded;
  }
  /**
   * @param int
   */
  public function setFilesetsCreated($filesetsCreated)
  {
    $this->filesetsCreated = $filesetsCreated;
  }
  /**
   * @return int
   */
  public function getFilesetsCreated()
  {
    return $this->filesetsCreated;
  }
  /**
   * @param int
   */
  public function setFilesetsDeleted($filesetsDeleted)
  {
    $this->filesetsDeleted = $filesetsDeleted;
  }
  /**
   * @return int
   */
  public function getFilesetsDeleted()
  {
    return $this->filesetsDeleted;
  }
  /**
   * @param int
   */
  public function setFilesetsUpdated($filesetsUpdated)
  {
    $this->filesetsUpdated = $filesetsUpdated;
  }
  /**
   * @return int
   */
  public function getFilesetsUpdated()
  {
    return $this->filesetsUpdated;
  }
  /**
   * @param int
   */
  public function setScannedFileCount($scannedFileCount)
  {
    $this->scannedFileCount = $scannedFileCount;
  }
  /**
   * @return int
   */
  public function getScannedFileCount()
  {
    return $this->scannedFileCount;
  }
  /**
   * @param int
   */
  public function setTablesCreated($tablesCreated)
  {
    $this->tablesCreated = $tablesCreated;
  }
  /**
   * @return int
   */
  public function getTablesCreated()
  {
    return $this->tablesCreated;
  }
  /**
   * @param int
   */
  public function setTablesDeleted($tablesDeleted)
  {
    $this->tablesDeleted = $tablesDeleted;
  }
  /**
   * @return int
   */
  public function getTablesDeleted()
  {
    return $this->tablesDeleted;
  }
  /**
   * @param int
   */
  public function setTablesUpdated($tablesUpdated)
  {
    $this->tablesUpdated = $tablesUpdated;
  }
  /**
   * @return int
   */
  public function getTablesUpdated()
  {
    return $this->tablesUpdated;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDataplexV1DataDiscoveryResultScanStatistics::class, 'Google_Service_CloudDataplex_GoogleCloudDataplexV1DataDiscoveryResultScanStatistics');
