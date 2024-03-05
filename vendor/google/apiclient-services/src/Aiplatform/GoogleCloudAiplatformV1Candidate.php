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

class GoogleCloudAiplatformV1Candidate extends \Google\Collection
{
  protected $collection_key = 'safetyRatings';
  protected $citationMetadataType = GoogleCloudAiplatformV1CitationMetadata::class;
  protected $citationMetadataDataType = '';
  protected $contentType = GoogleCloudAiplatformV1Content::class;
  protected $contentDataType = '';
  /**
   * @var string
   */
  public $finishMessage;
  /**
   * @var string
   */
  public $finishReason;
  protected $groundingMetadataType = GoogleCloudAiplatformV1GroundingMetadata::class;
  protected $groundingMetadataDataType = '';
  /**
   * @var int
   */
  public $index;
  protected $safetyRatingsType = GoogleCloudAiplatformV1SafetyRating::class;
  protected $safetyRatingsDataType = 'array';

  /**
   * @param GoogleCloudAiplatformV1CitationMetadata
   */
  public function setCitationMetadata(GoogleCloudAiplatformV1CitationMetadata $citationMetadata)
  {
    $this->citationMetadata = $citationMetadata;
  }
  /**
   * @return GoogleCloudAiplatformV1CitationMetadata
   */
  public function getCitationMetadata()
  {
    return $this->citationMetadata;
  }
  /**
   * @param GoogleCloudAiplatformV1Content
   */
  public function setContent(GoogleCloudAiplatformV1Content $content)
  {
    $this->content = $content;
  }
  /**
   * @return GoogleCloudAiplatformV1Content
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
   * @param GoogleCloudAiplatformV1GroundingMetadata
   */
  public function setGroundingMetadata(GoogleCloudAiplatformV1GroundingMetadata $groundingMetadata)
  {
    $this->groundingMetadata = $groundingMetadata;
  }
  /**
   * @return GoogleCloudAiplatformV1GroundingMetadata
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
   * @param GoogleCloudAiplatformV1SafetyRating[]
   */
  public function setSafetyRatings($safetyRatings)
  {
    $this->safetyRatings = $safetyRatings;
  }
  /**
   * @return GoogleCloudAiplatformV1SafetyRating[]
   */
  public function getSafetyRatings()
  {
    return $this->safetyRatings;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudAiplatformV1Candidate::class, 'Google_Service_Aiplatform_GoogleCloudAiplatformV1Candidate');
