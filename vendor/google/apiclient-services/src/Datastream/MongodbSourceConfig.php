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

namespace Google\Service\Datastream;

class MongodbSourceConfig extends \Google\Model
{
  protected $excludeObjectsType = MongodbCluster::class;
  protected $excludeObjectsDataType = '';
  protected $includeObjectsType = MongodbCluster::class;
  protected $includeObjectsDataType = '';
  /**
   * @var int
   */
  public $maxConcurrentBackfillTasks;

  /**
   * @param MongodbCluster
   */
  public function setExcludeObjects(MongodbCluster $excludeObjects)
  {
    $this->excludeObjects = $excludeObjects;
  }
  /**
   * @return MongodbCluster
   */
  public function getExcludeObjects()
  {
    return $this->excludeObjects;
  }
  /**
   * @param MongodbCluster
   */
  public function setIncludeObjects(MongodbCluster $includeObjects)
  {
    $this->includeObjects = $includeObjects;
  }
  /**
   * @return MongodbCluster
   */
  public function getIncludeObjects()
  {
    return $this->includeObjects;
  }
  /**
   * @param int
   */
  public function setMaxConcurrentBackfillTasks($maxConcurrentBackfillTasks)
  {
    $this->maxConcurrentBackfillTasks = $maxConcurrentBackfillTasks;
  }
  /**
   * @return int
   */
  public function getMaxConcurrentBackfillTasks()
  {
    return $this->maxConcurrentBackfillTasks;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(MongodbSourceConfig::class, 'Google_Service_Datastream_MongodbSourceConfig');
