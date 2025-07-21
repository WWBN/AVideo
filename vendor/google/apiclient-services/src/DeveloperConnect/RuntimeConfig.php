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

namespace Google\Service\DeveloperConnect;

class RuntimeConfig extends \Google\Model
{
  protected $appHubWorkloadType = AppHubWorkload::class;
  protected $appHubWorkloadDataType = '';
  protected $gkeWorkloadType = GKEWorkload::class;
  protected $gkeWorkloadDataType = '';
  /**
   * @var string
   */
  public $state;
  /**
   * @var string
   */
  public $uri;

  /**
   * @param AppHubWorkload
   */
  public function setAppHubWorkload(AppHubWorkload $appHubWorkload)
  {
    $this->appHubWorkload = $appHubWorkload;
  }
  /**
   * @return AppHubWorkload
   */
  public function getAppHubWorkload()
  {
    return $this->appHubWorkload;
  }
  /**
   * @param GKEWorkload
   */
  public function setGkeWorkload(GKEWorkload $gkeWorkload)
  {
    $this->gkeWorkload = $gkeWorkload;
  }
  /**
   * @return GKEWorkload
   */
  public function getGkeWorkload()
  {
    return $this->gkeWorkload;
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
  /**
   * @param string
   */
  public function setUri($uri)
  {
    $this->uri = $uri;
  }
  /**
   * @return string
   */
  public function getUri()
  {
    return $this->uri;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(RuntimeConfig::class, 'Google_Service_DeveloperConnect_RuntimeConfig');
