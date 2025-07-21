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

class GoogleCloudDialogflowCxV3ToolAuthentication extends \Google\Model
{
  protected $apiKeyConfigType = GoogleCloudDialogflowCxV3ToolAuthenticationApiKeyConfig::class;
  protected $apiKeyConfigDataType = '';
  protected $bearerTokenConfigType = GoogleCloudDialogflowCxV3ToolAuthenticationBearerTokenConfig::class;
  protected $bearerTokenConfigDataType = '';
  protected $oauthConfigType = GoogleCloudDialogflowCxV3ToolAuthenticationOAuthConfig::class;
  protected $oauthConfigDataType = '';
  protected $serviceAgentAuthConfigType = GoogleCloudDialogflowCxV3ToolAuthenticationServiceAgentAuthConfig::class;
  protected $serviceAgentAuthConfigDataType = '';

  /**
   * @param GoogleCloudDialogflowCxV3ToolAuthenticationApiKeyConfig
   */
  public function setApiKeyConfig(GoogleCloudDialogflowCxV3ToolAuthenticationApiKeyConfig $apiKeyConfig)
  {
    $this->apiKeyConfig = $apiKeyConfig;
  }
  /**
   * @return GoogleCloudDialogflowCxV3ToolAuthenticationApiKeyConfig
   */
  public function getApiKeyConfig()
  {
    return $this->apiKeyConfig;
  }
  /**
   * @param GoogleCloudDialogflowCxV3ToolAuthenticationBearerTokenConfig
   */
  public function setBearerTokenConfig(GoogleCloudDialogflowCxV3ToolAuthenticationBearerTokenConfig $bearerTokenConfig)
  {
    $this->bearerTokenConfig = $bearerTokenConfig;
  }
  /**
   * @return GoogleCloudDialogflowCxV3ToolAuthenticationBearerTokenConfig
   */
  public function getBearerTokenConfig()
  {
    return $this->bearerTokenConfig;
  }
  /**
   * @param GoogleCloudDialogflowCxV3ToolAuthenticationOAuthConfig
   */
  public function setOauthConfig(GoogleCloudDialogflowCxV3ToolAuthenticationOAuthConfig $oauthConfig)
  {
    $this->oauthConfig = $oauthConfig;
  }
  /**
   * @return GoogleCloudDialogflowCxV3ToolAuthenticationOAuthConfig
   */
  public function getOauthConfig()
  {
    return $this->oauthConfig;
  }
  /**
   * @param GoogleCloudDialogflowCxV3ToolAuthenticationServiceAgentAuthConfig
   */
  public function setServiceAgentAuthConfig(GoogleCloudDialogflowCxV3ToolAuthenticationServiceAgentAuthConfig $serviceAgentAuthConfig)
  {
    $this->serviceAgentAuthConfig = $serviceAgentAuthConfig;
  }
  /**
   * @return GoogleCloudDialogflowCxV3ToolAuthenticationServiceAgentAuthConfig
   */
  public function getServiceAgentAuthConfig()
  {
    return $this->serviceAgentAuthConfig;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDialogflowCxV3ToolAuthentication::class, 'Google_Service_Dialogflow_GoogleCloudDialogflowCxV3ToolAuthentication');
