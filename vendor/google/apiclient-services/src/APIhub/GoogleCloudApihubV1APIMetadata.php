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

namespace Google\Service\APIhub;

class GoogleCloudApihubV1APIMetadata extends \Google\Collection
{
  protected $collection_key = 'versions';
  protected $apiType = GoogleCloudApihubV1Api::class;
  protected $apiDataType = '';
  /**
   * @var string
   */
  public $originalCreateTime;
  /**
   * @var string
   */
  public $originalId;
  /**
   * @var string
   */
  public $originalUpdateTime;
  protected $versionsType = GoogleCloudApihubV1VersionMetadata::class;
  protected $versionsDataType = 'array';

  /**
   * @param GoogleCloudApihubV1Api
   */
  public function setApi(GoogleCloudApihubV1Api $api)
  {
    $this->api = $api;
  }
  /**
   * @return GoogleCloudApihubV1Api
   */
  public function getApi()
  {
    return $this->api;
  }
  /**
   * @param string
   */
  public function setOriginalCreateTime($originalCreateTime)
  {
    $this->originalCreateTime = $originalCreateTime;
  }
  /**
   * @return string
   */
  public function getOriginalCreateTime()
  {
    return $this->originalCreateTime;
  }
  /**
   * @param string
   */
  public function setOriginalId($originalId)
  {
    $this->originalId = $originalId;
  }
  /**
   * @return string
   */
  public function getOriginalId()
  {
    return $this->originalId;
  }
  /**
   * @param string
   */
  public function setOriginalUpdateTime($originalUpdateTime)
  {
    $this->originalUpdateTime = $originalUpdateTime;
  }
  /**
   * @return string
   */
  public function getOriginalUpdateTime()
  {
    return $this->originalUpdateTime;
  }
  /**
   * @param GoogleCloudApihubV1VersionMetadata[]
   */
  public function setVersions($versions)
  {
    $this->versions = $versions;
  }
  /**
   * @return GoogleCloudApihubV1VersionMetadata[]
   */
  public function getVersions()
  {
    return $this->versions;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1APIMetadata::class, 'Google_Service_APIhub_GoogleCloudApihubV1APIMetadata');
