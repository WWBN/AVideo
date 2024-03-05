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

class NlpSaftLangIdResult extends \Google\Collection
{
  protected $collection_key = 'spanPredictions';
  /**
   * @var string
   */
  public $modelVersion;
  protected $predictionsType = NlpSaftLanguageSpan::class;
  protected $predictionsDataType = 'array';
  protected $spanPredictionsType = NlpSaftLanguageSpanSequence::class;
  protected $spanPredictionsDataType = 'array';

  /**
   * @param string
   */
  public function setModelVersion($modelVersion)
  {
    $this->modelVersion = $modelVersion;
  }
  /**
   * @return string
   */
  public function getModelVersion()
  {
    return $this->modelVersion;
  }
  /**
   * @param NlpSaftLanguageSpan[]
   */
  public function setPredictions($predictions)
  {
    $this->predictions = $predictions;
  }
  /**
   * @return NlpSaftLanguageSpan[]
   */
  public function getPredictions()
  {
    return $this->predictions;
  }
  /**
   * @param NlpSaftLanguageSpanSequence[]
   */
  public function setSpanPredictions($spanPredictions)
  {
    $this->spanPredictions = $spanPredictions;
  }
  /**
   * @return NlpSaftLanguageSpanSequence[]
   */
  public function getSpanPredictions()
  {
    return $this->spanPredictions;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(NlpSaftLangIdResult::class, 'Google_Service_Aiplatform_NlpSaftLangIdResult');
