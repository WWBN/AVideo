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

class LearningGenaiRootCodeyCheckpoint extends \Google\Model
{
  protected $codeyTruncatorMetadataType = LearningGenaiRootCodeyTruncatorMetadata::class;
  protected $codeyTruncatorMetadataDataType = '';
  /**
   * @var string
   */
  public $currentSample;
  /**
   * @var string
   */
  public $postInferenceStep;

  /**
   * @param LearningGenaiRootCodeyTruncatorMetadata
   */
  public function setCodeyTruncatorMetadata(LearningGenaiRootCodeyTruncatorMetadata $codeyTruncatorMetadata)
  {
    $this->codeyTruncatorMetadata = $codeyTruncatorMetadata;
  }
  /**
   * @return LearningGenaiRootCodeyTruncatorMetadata
   */
  public function getCodeyTruncatorMetadata()
  {
    return $this->codeyTruncatorMetadata;
  }
  /**
   * @param string
   */
  public function setCurrentSample($currentSample)
  {
    $this->currentSample = $currentSample;
  }
  /**
   * @return string
   */
  public function getCurrentSample()
  {
    return $this->currentSample;
  }
  /**
   * @param string
   */
  public function setPostInferenceStep($postInferenceStep)
  {
    $this->postInferenceStep = $postInferenceStep;
  }
  /**
   * @return string
   */
  public function getPostInferenceStep()
  {
    return $this->postInferenceStep;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootCodeyCheckpoint::class, 'Google_Service_Aiplatform_LearningGenaiRootCodeyCheckpoint');
