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

namespace Google\Service\StorageBatchOperations;

class PutMetadata extends \Google\Model
{
  /**
   * @var string
   */
  public $cacheControl;
  /**
   * @var string
   */
  public $contentDisposition;
  /**
   * @var string
   */
  public $contentEncoding;
  /**
   * @var string
   */
  public $contentLanguage;
  /**
   * @var string
   */
  public $contentType;
  /**
   * @var string[]
   */
  public $customMetadata;
  /**
   * @var string
   */
  public $customTime;

  /**
   * @param string
   */
  public function setCacheControl($cacheControl)
  {
    $this->cacheControl = $cacheControl;
  }
  /**
   * @return string
   */
  public function getCacheControl()
  {
    return $this->cacheControl;
  }
  /**
   * @param string
   */
  public function setContentDisposition($contentDisposition)
  {
    $this->contentDisposition = $contentDisposition;
  }
  /**
   * @return string
   */
  public function getContentDisposition()
  {
    return $this->contentDisposition;
  }
  /**
   * @param string
   */
  public function setContentEncoding($contentEncoding)
  {
    $this->contentEncoding = $contentEncoding;
  }
  /**
   * @return string
   */
  public function getContentEncoding()
  {
    return $this->contentEncoding;
  }
  /**
   * @param string
   */
  public function setContentLanguage($contentLanguage)
  {
    $this->contentLanguage = $contentLanguage;
  }
  /**
   * @return string
   */
  public function getContentLanguage()
  {
    return $this->contentLanguage;
  }
  /**
   * @param string
   */
  public function setContentType($contentType)
  {
    $this->contentType = $contentType;
  }
  /**
   * @return string
   */
  public function getContentType()
  {
    return $this->contentType;
  }
  /**
   * @param string[]
   */
  public function setCustomMetadata($customMetadata)
  {
    $this->customMetadata = $customMetadata;
  }
  /**
   * @return string[]
   */
  public function getCustomMetadata()
  {
    return $this->customMetadata;
  }
  /**
   * @param string
   */
  public function setCustomTime($customTime)
  {
    $this->customTime = $customTime;
  }
  /**
   * @return string
   */
  public function getCustomTime()
  {
    return $this->customTime;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PutMetadata::class, 'Google_Service_StorageBatchOperations_PutMetadata');
