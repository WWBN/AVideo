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

class LearningGenaiRootClassifierOutputSummary extends \Google\Collection
{
  protected $collection_key = 'ruleOutputs';
  protected $metricsType = LearningGenaiRootMetricOutput::class;
  protected $metricsDataType = 'array';
  protected $ruleOutputType = LearningGenaiRootRuleOutput::class;
  protected $ruleOutputDataType = '';
  protected $ruleOutputsType = LearningGenaiRootRuleOutput::class;
  protected $ruleOutputsDataType = 'array';

  /**
   * @param LearningGenaiRootMetricOutput[]
   */
  public function setMetrics($metrics)
  {
    $this->metrics = $metrics;
  }
  /**
   * @return LearningGenaiRootMetricOutput[]
   */
  public function getMetrics()
  {
    return $this->metrics;
  }
  /**
   * @param LearningGenaiRootRuleOutput
   */
  public function setRuleOutput(LearningGenaiRootRuleOutput $ruleOutput)
  {
    $this->ruleOutput = $ruleOutput;
  }
  /**
   * @return LearningGenaiRootRuleOutput
   */
  public function getRuleOutput()
  {
    return $this->ruleOutput;
  }
  /**
   * @param LearningGenaiRootRuleOutput[]
   */
  public function setRuleOutputs($ruleOutputs)
  {
    $this->ruleOutputs = $ruleOutputs;
  }
  /**
   * @return LearningGenaiRootRuleOutput[]
   */
  public function getRuleOutputs()
  {
    return $this->ruleOutputs;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootClassifierOutputSummary::class, 'Google_Service_Aiplatform_LearningGenaiRootClassifierOutputSummary');
