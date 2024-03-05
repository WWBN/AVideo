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

class CloudAiNlLlmProtoServiceCandidate extends \Google\Collection
{
  protected $collection_key = 'safetyRatings';
  protected $citationMetadataType = CloudAiNlLlmProtoServiceCitationMetadata::class;
  protected $citationMetadataDataType = '';
  protected $contentType = CloudAiNlLlmProtoServiceContent::class;
  protected $contentDataType = '';
  /**
   * @var string
   */
  public $finishMessage;
  /**
   * @var string
   */
  public $finishReason;
  protected $groundingMetadataType = LearningGenaiRootGroundingMetadata::class;
  protected $groundingMetadataDataType = '';
  /**
   * @var int
   */
  public $index;
  protected $safetyRatingsType = CloudAiNlLlmProtoServiceSafetyRating::class;
  protected $safetyRatingsDataType = 'array';

  /**
   * @param CloudAiNlLlmProtoServiceCitationMetadata
   */
  public function setCitationMetadata(CloudAiNlLlmProtoServiceCitationMetadata $citationMetadata)
  {
    $this->citationMetadata = $citationMetadata;
  }
  /**
   * @return CloudAiNlLlmProtoServiceCitationMetadata
   */
  public function getCitationMetadata()
  {
    return $this->citationMetadata;
  }
  /**
   * @param CloudAiNlLlmProtoServiceContent
   */
  public function setContent(CloudAiNlLlmProtoServiceContent $content)
  {
    $this->content = $content;
  }
  /**
   * @return CloudAiNlLlmProtoServiceContent
   */
  public function getContent()
  {
    return $this->content;
  }
  /**
   * @param string
   */
  public function setFinishMessage($finishMessage)
  {
    $this->finishMessage = $finishMessage;
  }
  /**
   * @return string
   */
  public function getFinishMessage()
  {
    return $this->finishMessage;
  }
  /**
   * @param string
   */
  public function setFinishReason($finishReason)
  {
    $this->finishReason = $finishReason;
  }
  /**
   * @return string
   */
  public function getFinishReason()
  {
    return $this->finishReason;
  }
  /**
   * @param LearningGenaiRootGroundingMetadata
   */
  public function setGroundingMetadata(LearningGenaiRootGroundingMetadata $groundingMetadata)
  {
    $this->groundingMetadata = $groundingMetadata;
  }
  /**
   * @return LearningGenaiRootGroundingMetadata
   */
  public function getGroundingMetadata()
  {
    return $this->groundingMetadata;
  }
  /**
   * @param int
   */
  public function setIndex($index)
  {
    $this->index = $index;
  }
  /**
   * @return int
   */
  public function getIndex()
  {
    return $this->index;
  }
  /**
   * @param CloudAiNlLlmProtoServiceSafetyRating[]
   */
  public function setSafetyRatings($safetyRatings)
  {
    $this->safetyRatings = $safetyRatings;
  }
  /**
   * @return CloudAiNlLlmProtoServiceSafetyRating[]
   */
  public function getSafetyRatings()
  {
    return $this->safetyRatings;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CloudAiNlLlmProtoServiceCandidate::class, 'Google_Service_Aiplatform_CloudAiNlLlmProtoServiceCandidate');
