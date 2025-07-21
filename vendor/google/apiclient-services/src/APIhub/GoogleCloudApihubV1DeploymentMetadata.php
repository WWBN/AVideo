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

class GoogleCloudApihubV1DeploymentMetadata extends \Google\Model
{
  protected $deploymentType = GoogleCloudApihubV1Deployment::class;
  protected $deploymentDataType = '';
  /**
   * @var string
   */
  public $originalCreateTime;
  /**
   * @var string
   */
  public $originalId;
  /**
   * @var string
   */
  public $originalUpdateTime;

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
   * @param string
   */
  public function setOriginalCreateTime($originalCreateTime)
  {
    $this->originalCreateTime = $originalCreateTime;
  }
  /**
   * @return string
   */
  public function getOriginalCreateTime()
  {
    return $this->originalCreateTime;
  }
  /**
   * @param string
   */
  public function setOriginalId($originalId)
  {
    $this->originalId = $originalId;
  }
  /**
   * @return string
   */
  public function getOriginalId()
  {
    return $this->originalId;
  }
  /**
   * @param string
   */
  public function setOriginalUpdateTime($originalUpdateTime)
  {
    $this->originalUpdateTime = $originalUpdateTime;
  }
  /**
   * @return string
   */
  public function getOriginalUpdateTime()
  {
    return $this->originalUpdateTime;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1DeploymentMetadata::class, 'Google_Service_APIhub_GoogleCloudApihubV1DeploymentMetadata');
