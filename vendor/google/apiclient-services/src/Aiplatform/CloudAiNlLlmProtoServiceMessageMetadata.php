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

class CloudAiNlLlmProtoServiceMessageMetadata extends \Google\Collection
{
  protected $collection_key = 'outputFilterInfo';
  protected $inputFilterInfoType = LearningServingLlmMessageMetadata::class;
  protected $inputFilterInfoDataType = '';
  protected $modelRoutingDecisionType = LearningGenaiRootRoutingDecision::class;
  protected $modelRoutingDecisionDataType = '';
  protected $outputFilterInfoType = LearningServingLlmMessageMetadata::class;
  protected $outputFilterInfoDataType = 'array';

  /**
   * @param LearningServingLlmMessageMetadata
   */
  public function setInputFilterInfo(LearningServingLlmMessageMetadata $inputFilterInfo)
  {
    $this->inputFilterInfo = $inputFilterInfo;
  }
  /**
   * @return LearningServingLlmMessageMetadata
   */
  public function getInputFilterInfo()
  {
    return $this->inputFilterInfo;
  }
  /**
   * @param LearningGenaiRootRoutingDecision
   */
  public function setModelRoutingDecision(LearningGenaiRootRoutingDecision $modelRoutingDecision)
  {
    $this->modelRoutingDecision = $modelRoutingDecision;
  }
  /**
   * @return LearningGenaiRootRoutingDecision
   */
  public function getModelRoutingDecision()
  {
    return $this->modelRoutingDecision;
  }
  /**
   * @param LearningServingLlmMessageMetadata[]
   */
  public function setOutputFilterInfo($outputFilterInfo)
  {
    $this->outputFilterInfo = $outputFilterInfo;
  }
  /**
   * @return LearningServingLlmMessageMetadata[]
   */
  public function getOutputFilterInfo()
  {
    return $this->outputFilterInfo;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CloudAiNlLlmProtoServiceMessageMetadata::class, 'Google_Service_Aiplatform_CloudAiNlLlmProtoServiceMessageMetadata');
