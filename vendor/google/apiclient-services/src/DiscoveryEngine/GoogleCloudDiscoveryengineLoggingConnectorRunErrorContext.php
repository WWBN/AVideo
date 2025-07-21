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

namespace Google\Service\DiscoveryEngine;

class GoogleCloudDiscoveryengineLoggingConnectorRunErrorContext extends \Google\Model
{
  /**
   * @var string
   */
  public $connectorRun;
  /**
   * @var string
   */
  public $dataConnector;
  /**
   * @var string
   */
  public $endTime;
  /**
   * @var string
   */
  public $entity;
  /**
   * @var string
   */
  public $operation;
  /**
   * @var string
   */
  public $startTime;
  /**
   * @var string
   */
  public $syncType;

  /**
   * @param string
   */
  public function setConnectorRun($connectorRun)
  {
    $this->connectorRun = $connectorRun;
  }
  /**
   * @return string
   */
  public function getConnectorRun()
  {
    return $this->connectorRun;
  }
  /**
   * @param string
   */
  public function setDataConnector($dataConnector)
  {
    $this->dataConnector = $dataConnector;
  }
  /**
   * @return string
   */
  public function getDataConnector()
  {
    return $this->dataConnector;
  }
  /**
   * @param string
   */
  public function setEndTime($endTime)
  {
    $this->endTime = $endTime;
  }
  /**
   * @return string
   */
  public function getEndTime()
  {
    return $this->endTime;
  }
  /**
   * @param string
   */
  public function setEntity($entity)
  {
    $this->entity = $entity;
  }
  /**
   * @return string
   */
  public function getEntity()
  {
    return $this->entity;
  }
  /**
   * @param string
   */
  public function setOperation($operation)
  {
    $this->operation = $operation;
  }
  /**
   * @return string
   */
  public function getOperation()
  {
    return $this->operation;
  }
  /**
   * @param string
   */
  public function setStartTime($startTime)
  {
    $this->startTime = $startTime;
  }
  /**
   * @return string
   */
  public function getStartTime()
  {
    return $this->startTime;
  }
  /**
   * @param string
   */
  public function setSyncType($syncType)
  {
    $this->syncType = $syncType;
  }
  /**
   * @return string
   */
  public function getSyncType()
  {
    return $this->syncType;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDiscoveryengineLoggingConnectorRunErrorContext::class, 'Google_Service_DiscoveryEngine_GoogleCloudDiscoveryengineLoggingConnectorRunErrorContext');
