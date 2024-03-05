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

class LearningGenaiRootRoutingDecisionMetadata extends \Google\Model
{
  protected $scoreBasedRoutingMetadataType = LearningGenaiRootRoutingDecisionMetadataScoreBased::class;
  protected $scoreBasedRoutingMetadataDataType = '';
  protected $tokenLengthBasedRoutingMetadataType = LearningGenaiRootRoutingDecisionMetadataTokenLengthBased::class;
  protected $tokenLengthBasedRoutingMetadataDataType = '';

  /**
   * @param LearningGenaiRootRoutingDecisionMetadataScoreBased
   */
  public function setScoreBasedRoutingMetadata(LearningGenaiRootRoutingDecisionMetadataScoreBased $scoreBasedRoutingMetadata)
  {
    $this->scoreBasedRoutingMetadata = $scoreBasedRoutingMetadata;
  }
  /**
   * @return LearningGenaiRootRoutingDecisionMetadataScoreBased
   */
  public function getScoreBasedRoutingMetadata()
  {
    return $this->scoreBasedRoutingMetadata;
  }
  /**
   * @param LearningGenaiRootRoutingDecisionMetadataTokenLengthBased
   */
  public function setTokenLengthBasedRoutingMetadata(LearningGenaiRootRoutingDecisionMetadataTokenLengthBased $tokenLengthBasedRoutingMetadata)
  {
    $this->tokenLengthBasedRoutingMetadata = $tokenLengthBasedRoutingMetadata;
  }
  /**
   * @return LearningGenaiRootRoutingDecisionMetadataTokenLengthBased
   */
  public function getTokenLengthBasedRoutingMetadata()
  {
    return $this->tokenLengthBasedRoutingMetadata;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootRoutingDecisionMetadata::class, 'Google_Service_Aiplatform_LearningGenaiRootRoutingDecisionMetadata');
