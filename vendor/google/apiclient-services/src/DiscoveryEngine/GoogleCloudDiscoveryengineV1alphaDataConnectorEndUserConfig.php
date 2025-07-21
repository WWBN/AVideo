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

namespace Google\Service\DiscoveryEngine;

class GoogleCloudDiscoveryengineV1alphaDataConnectorEndUserConfig extends \Google\Model
{
  /**
   * @var array[]
   */
  public $additionalParams;
  /**
   * @var array[]
   */
  public $authParams;

  /**
   * @param array[]
   */
  public function setAdditionalParams($additionalParams)
  {
    $this->additionalParams = $additionalParams;
  }
  /**
   * @return array[]
   */
  public function getAdditionalParams()
  {
    return $this->additionalParams;
  }
  /**
   * @param array[]
   */
  public function setAuthParams($authParams)
  {
    $this->authParams = $authParams;
  }
  /**
   * @return array[]
   */
  public function getAuthParams()
  {
    return $this->authParams;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDiscoveryengineV1alphaDataConnectorEndUserConfig::class, 'Google_Service_DiscoveryEngine_GoogleCloudDiscoveryengineV1alphaDataConnectorEndUserConfig');
