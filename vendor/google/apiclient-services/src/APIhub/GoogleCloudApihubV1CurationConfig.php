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

class GoogleCloudApihubV1CurationConfig extends \Google\Model
{
  /**
   * @var string
   */
  public $curationType;
  protected $customCurationType = GoogleCloudApihubV1CustomCuration::class;
  protected $customCurationDataType = '';

  /**
   * @param string
   */
  public function setCurationType($curationType)
  {
    $this->curationType = $curationType;
  }
  /**
   * @return string
   */
  public function getCurationType()
  {
    return $this->curationType;
  }
  /**
   * @param GoogleCloudApihubV1CustomCuration
   */
  public function setCustomCuration(GoogleCloudApihubV1CustomCuration $customCuration)
  {
    $this->customCuration = $customCuration;
  }
  /**
   * @return GoogleCloudApihubV1CustomCuration
   */
  public function getCustomCuration()
  {
    return $this->customCuration;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1CurationConfig::class, 'Google_Service_APIhub_GoogleCloudApihubV1CurationConfig');
