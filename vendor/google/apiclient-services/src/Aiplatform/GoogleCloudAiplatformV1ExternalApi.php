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

class GoogleCloudAiplatformV1ExternalApi extends \Google\Model
{
  protected $apiAuthType = GoogleCloudAiplatformV1ApiAuth::class;
  protected $apiAuthDataType = '';
  /**
   * @var string
   */
  public $apiSpec;
  protected $authConfigType = GoogleCloudAiplatformV1AuthConfig::class;
  protected $authConfigDataType = '';
  protected $elasticSearchParamsType = GoogleCloudAiplatformV1ExternalApiElasticSearchParams::class;
  protected $elasticSearchParamsDataType = '';
  /**
   * @var string
   */
  public $endpoint;
  protected $simpleSearchParamsType = GoogleCloudAiplatformV1ExternalApiSimpleSearchParams::class;
  protected $simpleSearchParamsDataType = '';

  /**
   * @param GoogleCloudAiplatformV1ApiAuth
   */
  public function setApiAuth(GoogleCloudAiplatformV1ApiAuth $apiAuth)
  {
    $this->apiAuth = $apiAuth;
  }
  /**
   * @return GoogleCloudAiplatformV1ApiAuth
   */
  public function getApiAuth()
  {
    return $this->apiAuth;
  }
  /**
   * @param string
   */
  public function setApiSpec($apiSpec)
  {
    $this->apiSpec = $apiSpec;
  }
  /**
   * @return string
   */
  public function getApiSpec()
  {
    return $this->apiSpec;
  }
  /**
   * @param GoogleCloudAiplatformV1AuthConfig
   */
  public function setAuthConfig(GoogleCloudAiplatformV1AuthConfig $authConfig)
  {
    $this->authConfig = $authConfig;
  }
  /**
   * @return GoogleCloudAiplatformV1AuthConfig
   */
  public function getAuthConfig()
  {
    return $this->authConfig;
  }
  /**
   * @param GoogleCloudAiplatformV1ExternalApiElasticSearchParams
   */
  public function setElasticSearchParams(GoogleCloudAiplatformV1ExternalApiElasticSearchParams $elasticSearchParams)
  {
    $this->elasticSearchParams = $elasticSearchParams;
  }
  /**
   * @return GoogleCloudAiplatformV1ExternalApiElasticSearchParams
   */
  public function getElasticSearchParams()
  {
    return $this->elasticSearchParams;
  }
  /**
   * @param string
   */
  public function setEndpoint($endpoint)
  {
    $this->endpoint = $endpoint;
  }
  /**
   * @return string
   */
  public function getEndpoint()
  {
    return $this->endpoint;
  }
  /**
   * @param GoogleCloudAiplatformV1ExternalApiSimpleSearchParams
   */
  public function setSimpleSearchParams(GoogleCloudAiplatformV1ExternalApiSimpleSearchParams $simpleSearchParams)
  {
    $this->simpleSearchParams = $simpleSearchParams;
  }
  /**
   * @return GoogleCloudAiplatformV1ExternalApiSimpleSearchParams
   */
  public function getSimpleSearchParams()
  {
    return $this->simpleSearchParams;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudAiplatformV1ExternalApi::class, 'Google_Service_Aiplatform_GoogleCloudAiplatformV1ExternalApi');
