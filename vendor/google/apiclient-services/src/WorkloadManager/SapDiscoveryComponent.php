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

class SapDiscoveryComponent extends \Google\Collection
{
  protected $collection_key = 'resources';
  /**
   * @var string
   */
  public $applicationType;
  /**
   * @var string
   */
  public $databaseType;
  /**
   * @var string
   */
  public $hostProject;
  protected $resourcesType = SapDiscoveryResource::class;
  protected $resourcesDataType = 'array';
  /**
   * @var string
   */
  public $sid;

  /**
   * @param string
   */
  public function setApplicationType($applicationType)
  {
    $this->applicationType = $applicationType;
  }
  /**
   * @return string
   */
  public function getApplicationType()
  {
    return $this->applicationType;
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
  /**
   * @param string
   */
  public function setHostProject($hostProject)
  {
    $this->hostProject = $hostProject;
  }
  /**
   * @return string
   */
  public function getHostProject()
  {
    return $this->hostProject;
  }
  /**
   * @param SapDiscoveryResource[]
   */
  public function setResources($resources)
  {
    $this->resources = $resources;
  }
  /**
   * @return SapDiscoveryResource[]
   */
  public function getResources()
  {
    return $this->resources;
  }
  /**
   * @param string
   */
  public function setSid($sid)
  {
    $this->sid = $sid;
  }
  /**
   * @return string
   */
  public function getSid()
  {
    return $this->sid;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SapDiscoveryComponent::class, 'Google_Service_WorkloadManager_SapDiscoveryComponent');
