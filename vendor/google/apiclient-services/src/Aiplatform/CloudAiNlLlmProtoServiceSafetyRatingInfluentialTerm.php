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

class CloudAiNlLlmProtoServiceSafetyRatingInfluentialTerm extends \Google\Model
{
  /**
   * @var int
   */
  public $beginOffset;
  /**
   * @var float
   */
  public $confidence;
  /**
   * @var string
   */
  public $source;
  /**
   * @var string
   */
  public $term;

  /**
   * @param int
   */
  public function setBeginOffset($beginOffset)
  {
    $this->beginOffset = $beginOffset;
  }
  /**
   * @return int
   */
  public function getBeginOffset()
  {
    return $this->beginOffset;
  }
  /**
   * @param float
   */
  public function setConfidence($confidence)
  {
    $this->confidence = $confidence;
  }
  /**
   * @return float
   */
  public function getConfidence()
  {
    return $this->confidence;
  }
  /**
   * @param string
   */
  public function setSource($source)
  {
    $this->source = $source;
  }
  /**
   * @return string
   */
  public function getSource()
  {
    return $this->source;
  }
  /**
   * @param string
   */
  public function setTerm($term)
  {
    $this->term = $term;
  }
  /**
   * @return string
   */
  public function getTerm()
  {
    return $this->term;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CloudAiNlLlmProtoServiceSafetyRatingInfluentialTerm::class, 'Google_Service_Aiplatform_CloudAiNlLlmProtoServiceSafetyRatingInfluentialTerm');
