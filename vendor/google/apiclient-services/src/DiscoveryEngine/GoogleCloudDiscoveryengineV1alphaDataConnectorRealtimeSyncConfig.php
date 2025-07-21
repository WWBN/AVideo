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

class GoogleCloudDiscoveryengineV1alphaDataConnectorRealtimeSyncConfig extends \Google\Model
{
  /**
   * @var string
   */
  public $realtimeSyncSecret;
  /**
   * @var string
   */
  public $webhookUri;

  /**
   * @param string
   */
  public function setRealtimeSyncSecret($realtimeSyncSecret)
  {
    $this->realtimeSyncSecret = $realtimeSyncSecret;
  }
  /**
   * @return string
   */
  public function getRealtimeSyncSecret()
  {
    return $this->realtimeSyncSecret;
  }
  /**
   * @param string
   */
  public function setWebhookUri($webhookUri)
  {
    $this->webhookUri = $webhookUri;
  }
  /**
   * @return string
   */
  public function getWebhookUri()
  {
    return $this->webhookUri;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDiscoveryengineV1alphaDataConnectorRealtimeSyncConfig::class, 'Google_Service_DiscoveryEngine_GoogleCloudDiscoveryengineV1alphaDataConnectorRealtimeSyncConfig');
