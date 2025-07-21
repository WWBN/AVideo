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

class GoogleCloudApihubV1ApiHubResource extends \Google\Model
{
  protected $apiType = GoogleCloudApihubV1Api::class;
  protected $apiDataType = '';
  protected $definitionType = GoogleCloudApihubV1Definition::class;
  protected $definitionDataType = '';
  protected $deploymentType = GoogleCloudApihubV1Deployment::class;
  protected $deploymentDataType = '';
  protected $operationType = GoogleCloudApihubV1ApiOperation::class;
  protected $operationDataType = '';
  protected $specType = GoogleCloudApihubV1Spec::class;
  protected $specDataType = '';
  protected $versionType = GoogleCloudApihubV1Version::class;
  protected $versionDataType = '';

  /**
   * @param GoogleCloudApihubV1Api
   */
  public function setApi(GoogleCloudApihubV1Api $api)
  {
    $this->api = $api;
  }
  /**
   * @return GoogleCloudApihubV1Api
   */
  public function getApi()
  {
    return $this->api;
  }
  /**
   * @param GoogleCloudApihubV1Definition
   */
  public function setDefinition(GoogleCloudApihubV1Definition $definition)
  {
    $this->definition = $definition;
  }
  /**
   * @return GoogleCloudApihubV1Definition
   */
  public function getDefinition()
  {
    return $this->definition;
  }
  /**
   * @param GoogleCloudApihubV1Deployment
   */
  public function setDeployment(GoogleCloudApihubV1Deployment $deployment)
  {
    $this->deployment = $deployment;
  }
  /**
   * @return GoogleCloudApihubV1Deployment
   */
  public function getDeployment()
  {
    return $this->deployment;
  }
  /**
   * @param GoogleCloudApihubV1ApiOperation
   */
  public function setOperation(GoogleCloudApihubV1ApiOperation $operation)
  {
    $this->operation = $operation;
  }
  /**
   * @return GoogleCloudApihubV1ApiOperation
   */
  public function getOperation()
  {
    return $this->operation;
  }
  /**
   * @param GoogleCloudApihubV1Spec
   */
  public function setSpec(GoogleCloudApihubV1Spec $spec)
  {
    $this->spec = $spec;
  }
  /**
   * @return GoogleCloudApihubV1Spec
   */
  public function getSpec()
  {
    return $this->spec;
  }
  /**
   * @param GoogleCloudApihubV1Version
   */
  public function setVersion(GoogleCloudApihubV1Version $version)
  {
    $this->version = $version;
  }
  /**
   * @return GoogleCloudApihubV1Version
   */
  public function getVersion()
  {
    return $this->version;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1ApiHubResource::class, 'Google_Service_APIhub_GoogleCloudApihubV1ApiHubResource');
