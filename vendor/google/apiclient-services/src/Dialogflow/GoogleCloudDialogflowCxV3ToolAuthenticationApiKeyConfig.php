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

namespace Google\Service\Dialogflow;

class GoogleCloudDialogflowCxV3ToolAuthenticationApiKeyConfig extends \Google\Model
{
  /**
   * @var string
   */
  public $apiKey;
  /**
   * @var string
   */
  public $keyName;
  /**
   * @var string
   */
  public $requestLocation;
  /**
   * @var string
   */
  public $secretVersionForApiKey;

  /**
   * @param string
   */
  public function setApiKey($apiKey)
  {
    $this->apiKey = $apiKey;
  }
  /**
   * @return string
   */
  public function getApiKey()
  {
    return $this->apiKey;
  }
  /**
   * @param string
   */
  public function setKeyName($keyName)
  {
    $this->keyName = $keyName;
  }
  /**
   * @return string
   */
  public function getKeyName()
  {
    return $this->keyName;
  }
  /**
   * @param string
   */
  public function setRequestLocation($requestLocation)
  {
    $this->requestLocation = $requestLocation;
  }
  /**
   * @return string
   */
  public function getRequestLocation()
  {
    return $this->requestLocation;
  }
  /**
   * @param string
   */
  public function setSecretVersionForApiKey($secretVersionForApiKey)
  {
    $this->secretVersionForApiKey = $secretVersionForApiKey;
  }
  /**
   * @return string
   */
  public function getSecretVersionForApiKey()
  {
    return $this->secretVersionForApiKey;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDialogflowCxV3ToolAuthenticationApiKeyConfig::class, 'Google_Service_Dialogflow_GoogleCloudDialogflowCxV3ToolAuthenticationApiKeyConfig');
