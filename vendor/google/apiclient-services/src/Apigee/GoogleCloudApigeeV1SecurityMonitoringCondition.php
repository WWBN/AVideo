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

namespace Google\Service\Apigee;

class GoogleCloudApigeeV1SecurityMonitoringCondition extends \Google\Model
{
  /**
   * @var string
   */
  public $createTime;
  protected $includeType = GoogleCloudApigeeV1BatchComputeSecurityAssessmentResultsRequestResourceArray::class;
  protected $includeDataType = '';
  protected $includeAllResourcesType = GoogleCloudApigeeV1BatchComputeSecurityAssessmentResultsRequestIncludeAll::class;
  protected $includeAllResourcesDataType = '';
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $profile;
  /**
   * @var string
   */
  public $scope;
  /**
   * @var int
   */
  public $totalDeployedResources;
  /**
   * @var int
   */
  public $totalMonitoredResources;
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param string
   */
  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  /**
   * @return string
   */
  public function getCreateTime()
  {
    return $this->createTime;
  }
  /**
   * @param GoogleCloudApigeeV1BatchComputeSecurityAssessmentResultsRequestResourceArray
   */
  public function setInclude(GoogleCloudApigeeV1BatchComputeSecurityAssessmentResultsRequestResourceArray $include)
  {
    $this->include = $include;
  }
  /**
   * @return GoogleCloudApigeeV1BatchComputeSecurityAssessmentResultsRequestResourceArray
   */
  public function getInclude()
  {
    return $this->include;
  }
  /**
   * @param GoogleCloudApigeeV1BatchComputeSecurityAssessmentResultsRequestIncludeAll
   */
  public function setIncludeAllResources(GoogleCloudApigeeV1BatchComputeSecurityAssessmentResultsRequestIncludeAll $includeAllResources)
  {
    $this->includeAllResources = $includeAllResources;
  }
  /**
   * @return GoogleCloudApigeeV1BatchComputeSecurityAssessmentResultsRequestIncludeAll
   */
  public function getIncludeAllResources()
  {
    return $this->includeAllResources;
  }
  /**
   * @param string
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param string
   */
  public function setProfile($profile)
  {
    $this->profile = $profile;
  }
  /**
   * @return string
   */
  public function getProfile()
  {
    return $this->profile;
  }
  /**
   * @param string
   */
  public function setScope($scope)
  {
    $this->scope = $scope;
  }
  /**
   * @return string
   */
  public function getScope()
  {
    return $this->scope;
  }
  /**
   * @param int
   */
  public function setTotalDeployedResources($totalDeployedResources)
  {
    $this->totalDeployedResources = $totalDeployedResources;
  }
  /**
   * @return int
   */
  public function getTotalDeployedResources()
  {
    return $this->totalDeployedResources;
  }
  /**
   * @param int
   */
  public function setTotalMonitoredResources($totalMonitoredResources)
  {
    $this->totalMonitoredResources = $totalMonitoredResources;
  }
  /**
   * @return int
   */
  public function getTotalMonitoredResources()
  {
    return $this->totalMonitoredResources;
  }
  /**
   * @param string
   */
  public function setUpdateTime($updateTime)
  {
    $this->updateTime = $updateTime;
  }
  /**
   * @return string
   */
  public function getUpdateTime()
  {
    return $this->updateTime;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApigeeV1SecurityMonitoringCondition::class, 'Google_Service_Apigee_GoogleCloudApigeeV1SecurityMonitoringCondition');
