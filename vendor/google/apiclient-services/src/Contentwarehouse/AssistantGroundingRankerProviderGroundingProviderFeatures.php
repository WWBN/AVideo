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

namespace Google\Service\Contentwarehouse;

class AssistantGroundingRankerProviderGroundingProviderFeatures extends \Google\Collection
{
  protected $collection_key = 'providerClusterIds';
  /**
   * @var string[]
   */
  public $providerClusterIds;
  protected $providerIdType = AssistantContextProviderId::class;
  protected $providerIdDataType = '';
  protected $providerSignalResultType = AssistantGroundingProviderProviderSignalResult::class;
  protected $providerSignalResultDataType = '';
  /**
   * @var float
   */
  public $pslScore;

  /**
   * @param string[]
   */
  public function setProviderClusterIds($providerClusterIds)
  {
    $this->providerClusterIds = $providerClusterIds;
  }
  /**
   * @return string[]
   */
  public function getProviderClusterIds()
  {
    return $this->providerClusterIds;
  }
  /**
   * @param AssistantContextProviderId
   */
  public function setProviderId(AssistantContextProviderId $providerId)
  {
    $this->providerId = $providerId;
  }
  /**
   * @return AssistantContextProviderId
   */
  public function getProviderId()
  {
    return $this->providerId;
  }
  /**
   * @param AssistantGroundingProviderProviderSignalResult
   */
  public function setProviderSignalResult(AssistantGroundingProviderProviderSignalResult $providerSignalResult)
  {
    $this->providerSignalResult = $providerSignalResult;
  }
  /**
   * @return AssistantGroundingProviderProviderSignalResult
   */
  public function getProviderSignalResult()
  {
    return $this->providerSignalResult;
  }
  /**
   * @param float
   */
  public function setPslScore($pslScore)
  {
    $this->pslScore = $pslScore;
  }
  /**
   * @return float
   */
  public function getPslScore()
  {
    return $this->pslScore;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantGroundingRankerProviderGroundingProviderFeatures::class, 'Google_Service_Contentwarehouse_AssistantGroundingRankerProviderGroundingProviderFeatures');
