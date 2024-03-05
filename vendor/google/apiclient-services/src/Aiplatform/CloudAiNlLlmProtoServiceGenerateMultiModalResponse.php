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

class CloudAiNlLlmProtoServiceGenerateMultiModalResponse extends \Google\Collection
{
  protected $collection_key = 'facts';
  protected $candidatesType = CloudAiNlLlmProtoServiceCandidate::class;
  protected $candidatesDataType = 'array';
  protected $debugMetadataType = CloudAiNlLlmProtoServiceMessageMetadata::class;
  protected $debugMetadataDataType = '';
  protected $factsType = CloudAiNlLlmProtoServiceFact::class;
  protected $factsDataType = 'array';
  protected $promptFeedbackType = CloudAiNlLlmProtoServicePromptFeedback::class;
  protected $promptFeedbackDataType = '';
  protected $reportingMetricsType = IntelligenceCloudAutomlXpsReportingMetrics::class;
  protected $reportingMetricsDataType = '';
  protected $usageMetadataType = CloudAiNlLlmProtoServiceUsageMetadata::class;
  protected $usageMetadataDataType = '';

  /**
   * @param CloudAiNlLlmProtoServiceCandidate[]
   */
  public function setCandidates($candidates)
  {
    $this->candidates = $candidates;
  }
  /**
   * @return CloudAiNlLlmProtoServiceCandidate[]
   */
  public function getCandidates()
  {
    return $this->candidates;
  }
  /**
   * @param CloudAiNlLlmProtoServiceMessageMetadata
   */
  public function setDebugMetadata(CloudAiNlLlmProtoServiceMessageMetadata $debugMetadata)
  {
    $this->debugMetadata = $debugMetadata;
  }
  /**
   * @return CloudAiNlLlmProtoServiceMessageMetadata
   */
  public function getDebugMetadata()
  {
    return $this->debugMetadata;
  }
  /**
   * @param CloudAiNlLlmProtoServiceFact[]
   */
  public function setFacts($facts)
  {
    $this->facts = $facts;
  }
  /**
   * @return CloudAiNlLlmProtoServiceFact[]
   */
  public function getFacts()
  {
    return $this->facts;
  }
  /**
   * @param CloudAiNlLlmProtoServicePromptFeedback
   */
  public function setPromptFeedback(CloudAiNlLlmProtoServicePromptFeedback $promptFeedback)
  {
    $this->promptFeedback = $promptFeedback;
  }
  /**
   * @return CloudAiNlLlmProtoServicePromptFeedback
   */
  public function getPromptFeedback()
  {
    return $this->promptFeedback;
  }
  /**
   * @param IntelligenceCloudAutomlXpsReportingMetrics
   */
  public function setReportingMetrics(IntelligenceCloudAutomlXpsReportingMetrics $reportingMetrics)
  {
    $this->reportingMetrics = $reportingMetrics;
  }
  /**
   * @return IntelligenceCloudAutomlXpsReportingMetrics
   */
  public function getReportingMetrics()
  {
    return $this->reportingMetrics;
  }
  /**
   * @param CloudAiNlLlmProtoServiceUsageMetadata
   */
  public function setUsageMetadata(CloudAiNlLlmProtoServiceUsageMetadata $usageMetadata)
  {
    $this->usageMetadata = $usageMetadata;
  }
  /**
   * @return CloudAiNlLlmProtoServiceUsageMetadata
   */
  public function getUsageMetadata()
  {
    return $this->usageMetadata;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CloudAiNlLlmProtoServiceGenerateMultiModalResponse::class, 'Google_Service_Aiplatform_CloudAiNlLlmProtoServiceGenerateMultiModalResponse');
