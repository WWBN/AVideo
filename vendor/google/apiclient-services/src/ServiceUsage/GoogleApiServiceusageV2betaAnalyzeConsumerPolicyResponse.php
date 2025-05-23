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

namespace Google\Service\ServiceUsage;

class GoogleApiServiceusageV2betaAnalyzeConsumerPolicyResponse extends \Google\Collection
{
  protected $collection_key = 'analysis';
  protected $analysisType = GoogleApiServiceusageV2betaAnalysis::class;
  protected $analysisDataType = 'array';

  /**
   * @param GoogleApiServiceusageV2betaAnalysis[]
   */
  public function setAnalysis($analysis)
  {
    $this->analysis = $analysis;
  }
  /**
   * @return GoogleApiServiceusageV2betaAnalysis[]
   */
  public function getAnalysis()
  {
    return $this->analysis;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleApiServiceusageV2betaAnalyzeConsumerPolicyResponse::class, 'Google_Service_ServiceUsage_GoogleApiServiceusageV2betaAnalyzeConsumerPolicyResponse');
