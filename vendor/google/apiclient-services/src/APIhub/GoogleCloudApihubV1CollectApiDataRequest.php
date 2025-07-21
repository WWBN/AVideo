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

class GoogleCloudApihubV1CollectApiDataRequest extends \Google\Model
{
  /**
   * @var string
   */
  public $actionId;
  protected $apiDataType = GoogleCloudApihubV1ApiData::class;
  protected $apiDataDataType = '';
  /**
   * @var string
   */
  public $collectionType;
  /**
   * @var string
   */
  public $pluginInstance;

  /**
   * @param string
   */
  public function setActionId($actionId)
  {
    $this->actionId = $actionId;
  }
  /**
   * @return string
   */
  public function getActionId()
  {
    return $this->actionId;
  }
  /**
   * @param GoogleCloudApihubV1ApiData
   */
  public function setApiData(GoogleCloudApihubV1ApiData $apiData)
  {
    $this->apiData = $apiData;
  }
  /**
   * @return GoogleCloudApihubV1ApiData
   */
  public function getApiData()
  {
    return $this->apiData;
  }
  /**
   * @param string
   */
  public function setCollectionType($collectionType)
  {
    $this->collectionType = $collectionType;
  }
  /**
   * @return string
   */
  public function getCollectionType()
  {
    return $this->collectionType;
  }
  /**
   * @param string
   */
  public function setPluginInstance($pluginInstance)
  {
    $this->pluginInstance = $pluginInstance;
  }
  /**
   * @return string
   */
  public function getPluginInstance()
  {
    return $this->pluginInstance;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1CollectApiDataRequest::class, 'Google_Service_APIhub_GoogleCloudApihubV1CollectApiDataRequest');
