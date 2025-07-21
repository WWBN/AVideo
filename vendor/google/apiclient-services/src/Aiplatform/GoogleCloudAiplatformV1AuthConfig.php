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

namespace Google\Service\Aiplatform;

class GoogleCloudAiplatformV1AuthConfig extends \Google\Model
{
  protected $apiKeyConfigType = GoogleCloudAiplatformV1AuthConfigApiKeyConfig::class;
  protected $apiKeyConfigDataType = '';
  /**
   * @var string
   */
  public $authType;
  protected $googleServiceAccountConfigType = GoogleCloudAiplatformV1AuthConfigGoogleServiceAccountConfig::class;
  protected $googleServiceAccountConfigDataType = '';
  protected $httpBasicAuthConfigType = GoogleCloudAiplatformV1AuthConfigHttpBasicAuthConfig::class;
  protected $httpBasicAuthConfigDataType = '';
  protected $oauthConfigType = GoogleCloudAiplatformV1AuthConfigOauthConfig::class;
  protected $oauthConfigDataType = '';
  protected $oidcConfigType = GoogleCloudAiplatformV1AuthConfigOidcConfig::class;
  protected $oidcConfigDataType = '';

  /**
   * @param GoogleCloudAiplatformV1AuthConfigApiKeyConfig
   */
  public function setApiKeyConfig(GoogleCloudAiplatformV1AuthConfigApiKeyConfig $apiKeyConfig)
  {
    $this->apiKeyConfig = $apiKeyConfig;
  }
  /**
   * @return GoogleCloudAiplatformV1AuthConfigApiKeyConfig
   */
  public function getApiKeyConfig()
  {
    return $this->apiKeyConfig;
  }
  /**
   * @param string
   */
  public function setAuthType($authType)
  {
    $this->authType = $authType;
  }
  /**
   * @return string
   */
  public function getAuthType()
  {
    return $this->authType;
  }
  /**
   * @param GoogleCloudAiplatformV1AuthConfigGoogleServiceAccountConfig
   */
  public function setGoogleServiceAccountConfig(GoogleCloudAiplatformV1AuthConfigGoogleServiceAccountConfig $googleServiceAccountConfig)
  {
    $this->googleServiceAccountConfig = $googleServiceAccountConfig;
  }
  /**
   * @return GoogleCloudAiplatformV1AuthConfigGoogleServiceAccountConfig
   */
  public function getGoogleServiceAccountConfig()
  {
    return $this->googleServiceAccountConfig;
  }
  /**
   * @param GoogleCloudAiplatformV1AuthConfigHttpBasicAuthConfig
   */
  public function setHttpBasicAuthConfig(GoogleCloudAiplatformV1AuthConfigHttpBasicAuthConfig $httpBasicAuthConfig)
  {
    $this->httpBasicAuthConfig = $httpBasicAuthConfig;
  }
  /**
   * @return GoogleCloudAiplatformV1AuthConfigHttpBasicAuthConfig
   */
  public function getHttpBasicAuthConfig()
  {
    return $this->httpBasicAuthConfig;
  }
  /**
   * @param GoogleCloudAiplatformV1AuthConfigOauthConfig
   */
  public function setOauthConfig(GoogleCloudAiplatformV1AuthConfigOauthConfig $oauthConfig)
  {
    $this->oauthConfig = $oauthConfig;
  }
  /**
   * @return GoogleCloudAiplatformV1AuthConfigOauthConfig
   */
  public function getOauthConfig()
  {
    return $this->oauthConfig;
  }
  /**
   * @param GoogleCloudAiplatformV1AuthConfigOidcConfig
   */
  public function setOidcConfig(GoogleCloudAiplatformV1AuthConfigOidcConfig $oidcConfig)
  {
    $this->oidcConfig = $oidcConfig;
  }
  /**
   * @return GoogleCloudAiplatformV1AuthConfigOidcConfig
   */
  public function getOidcConfig()
  {
    return $this->oidcConfig;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudAiplatformV1AuthConfig::class, 'Google_Service_Aiplatform_GoogleCloudAiplatformV1AuthConfig');
