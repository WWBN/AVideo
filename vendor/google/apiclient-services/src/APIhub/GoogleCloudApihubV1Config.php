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

class GoogleCloudApihubV1Config extends \Google\Model
{
  /**
   * @var string
   */
  public $cmekKeyName;
  /**
   * @var bool
   */
  public $disableSearch;
  /**
   * @var string
   */
  public $encryptionType;
  /**
   * @var string
   */
  public $vertexLocation;

  /**
   * @param string
   */
  public function setCmekKeyName($cmekKeyName)
  {
    $this->cmekKeyName = $cmekKeyName;
  }
  /**
   * @return string
   */
  public function getCmekKeyName()
  {
    return $this->cmekKeyName;
  }
  /**
   * @param bool
   */
  public function setDisableSearch($disableSearch)
  {
    $this->disableSearch = $disableSearch;
  }
  /**
   * @return bool
   */
  public function getDisableSearch()
  {
    return $this->disableSearch;
  }
  /**
   * @param string
   */
  public function setEncryptionType($encryptionType)
  {
    $this->encryptionType = $encryptionType;
  }
  /**
   * @return string
   */
  public function getEncryptionType()
  {
    return $this->encryptionType;
  }
  /**
   * @param string
   */
  public function setVertexLocation($vertexLocation)
  {
    $this->vertexLocation = $vertexLocation;
  }
  /**
   * @return string
   */
  public function getVertexLocation()
  {
    return $this->vertexLocation;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1Config::class, 'Google_Service_APIhub_GoogleCloudApihubV1Config');
