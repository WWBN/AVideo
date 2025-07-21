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

class GoogleCloudApihubV1Issue extends \Google\Collection
{
  protected $collection_key = 'path';
  /**
   * @var string
   */
  public $code;
  /**
   * @var string
   */
  public $message;
  /**
   * @var string[]
   */
  public $path;
  protected $rangeType = GoogleCloudApihubV1Range::class;
  protected $rangeDataType = '';
  /**
   * @var string
   */
  public $severity;

  /**
   * @param string
   */
  public function setCode($code)
  {
    $this->code = $code;
  }
  /**
   * @return string
   */
  public function getCode()
  {
    return $this->code;
  }
  /**
   * @param string
   */
  public function setMessage($message)
  {
    $this->message = $message;
  }
  /**
   * @return string
   */
  public function getMessage()
  {
    return $this->message;
  }
  /**
   * @param string[]
   */
  public function setPath($path)
  {
    $this->path = $path;
  }
  /**
   * @return string[]
   */
  public function getPath()
  {
    return $this->path;
  }
  /**
   * @param GoogleCloudApihubV1Range
   */
  public function setRange(GoogleCloudApihubV1Range $range)
  {
    $this->range = $range;
  }
  /**
   * @return GoogleCloudApihubV1Range
   */
  public function getRange()
  {
    return $this->range;
  }
  /**
   * @param string
   */
  public function setSeverity($severity)
  {
    $this->severity = $severity;
  }
  /**
   * @return string
   */
  public function getSeverity()
  {
    return $this->severity;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1Issue::class, 'Google_Service_APIhub_GoogleCloudApihubV1Issue');
