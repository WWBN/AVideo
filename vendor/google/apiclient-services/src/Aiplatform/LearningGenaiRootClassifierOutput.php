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

class LearningGenaiRootClassifierOutput extends \Google\Collection
{
  protected $collection_key = 'ruleOutputs';
  protected $ruleOutputType = LearningGenaiRootRuleOutput::class;
  protected $ruleOutputDataType = '';
  protected $ruleOutputsType = LearningGenaiRootRuleOutput::class;
  protected $ruleOutputsDataType = 'array';
  protected $stateType = LearningGenaiRootClassifierState::class;
  protected $stateDataType = '';

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
  /**
   * @param LearningGenaiRootClassifierState
   */
  public function setState(LearningGenaiRootClassifierState $state)
  {
    $this->state = $state;
  }
  /**
   * @return LearningGenaiRootClassifierState
   */
  public function getState()
  {
    return $this->state;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootClassifierOutput::class, 'Google_Service_Aiplatform_LearningGenaiRootClassifierOutput');
