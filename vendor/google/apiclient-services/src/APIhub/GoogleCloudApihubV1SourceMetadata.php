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

class GoogleCloudApihubV1SourceMetadata extends \Google\Model
{
  /**
   * @var string
   */
  public $originalResourceCreateTime;
  /**
   * @var string
   */
  public $originalResourceId;
  /**
   * @var string
   */
  public $originalResourceUpdateTime;
  protected $pluginInstanceActionSourceType = GoogleCloudApihubV1PluginInstanceActionSource::class;
  protected $pluginInstanceActionSourceDataType = '';
  /**
   * @var string
   */
  public $sourceType;

  /**
   * @param string
   */
  public function setOriginalResourceCreateTime($originalResourceCreateTime)
  {
    $this->originalResourceCreateTime = $originalResourceCreateTime;
  }
  /**
   * @return string
   */
  public function getOriginalResourceCreateTime()
  {
    return $this->originalResourceCreateTime;
  }
  /**
   * @param string
   */
  public function setOriginalResourceId($originalResourceId)
  {
    $this->originalResourceId = $originalResourceId;
  }
  /**
   * @return string
   */
  public function getOriginalResourceId()
  {
    return $this->originalResourceId;
  }
  /**
   * @param string
   */
  public function setOriginalResourceUpdateTime($originalResourceUpdateTime)
  {
    $this->originalResourceUpdateTime = $originalResourceUpdateTime;
  }
  /**
   * @return string
   */
  public function getOriginalResourceUpdateTime()
  {
    return $this->originalResourceUpdateTime;
  }
  /**
   * @param GoogleCloudApihubV1PluginInstanceActionSource
   */
  public function setPluginInstanceActionSource(GoogleCloudApihubV1PluginInstanceActionSource $pluginInstanceActionSource)
  {
    $this->pluginInstanceActionSource = $pluginInstanceActionSource;
  }
  /**
   * @return GoogleCloudApihubV1PluginInstanceActionSource
   */
  public function getPluginInstanceActionSource()
  {
    return $this->pluginInstanceActionSource;
  }
  /**
   * @param string
   */
  public function setSourceType($sourceType)
  {
    $this->sourceType = $sourceType;
  }
  /**
   * @return string
   */
  public function getSourceType()
  {
    return $this->sourceType;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1SourceMetadata::class, 'Google_Service_APIhub_GoogleCloudApihubV1SourceMetadata');
