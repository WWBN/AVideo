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

namespace Google\Service\Dialogflow;

class GoogleCloudDialogflowCxV3Playbook extends \Google\Collection
{
  protected $collection_key = 'referencedTools';
  /**
   * @var string
   */
  public $createTime;
  /**
   * @var string
   */
  public $displayName;
  /**
   * @var string
   */
  public $goal;
  protected $instructionType = GoogleCloudDialogflowCxV3PlaybookInstruction::class;
  protected $instructionDataType = '';
  protected $llmModelSettingsType = GoogleCloudDialogflowCxV3LlmModelSettings::class;
  protected $llmModelSettingsDataType = '';
  /**
   * @var string
   */
  public $name;
  /**
   * @var string[]
   */
  public $referencedFlows;
  /**
   * @var string[]
   */
  public $referencedPlaybooks;
  /**
   * @var string[]
   */
  public $referencedTools;
  /**
   * @var string
   */
  public $tokenCount;
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param string
   */
  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  /**
   * @return string
   */
  public function getCreateTime()
  {
    return $this->createTime;
  }
  /**
   * @param string
   */
  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  /**
   * @return string
   */
  public function getDisplayName()
  {
    return $this->displayName;
  }
  /**
   * @param string
   */
  public function setGoal($goal)
  {
    $this->goal = $goal;
  }
  /**
   * @return string
   */
  public function getGoal()
  {
    return $this->goal;
  }
  /**
   * @param GoogleCloudDialogflowCxV3PlaybookInstruction
   */
  public function setInstruction(GoogleCloudDialogflowCxV3PlaybookInstruction $instruction)
  {
    $this->instruction = $instruction;
  }
  /**
   * @return GoogleCloudDialogflowCxV3PlaybookInstruction
   */
  public function getInstruction()
  {
    return $this->instruction;
  }
  /**
   * @param GoogleCloudDialogflowCxV3LlmModelSettings
   */
  public function setLlmModelSettings(GoogleCloudDialogflowCxV3LlmModelSettings $llmModelSettings)
  {
    $this->llmModelSettings = $llmModelSettings;
  }
  /**
   * @return GoogleCloudDialogflowCxV3LlmModelSettings
   */
  public function getLlmModelSettings()
  {
    return $this->llmModelSettings;
  }
  /**
   * @param string
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param string[]
   */
  public function setReferencedFlows($referencedFlows)
  {
    $this->referencedFlows = $referencedFlows;
  }
  /**
   * @return string[]
   */
  public function getReferencedFlows()
  {
    return $this->referencedFlows;
  }
  /**
   * @param string[]
   */
  public function setReferencedPlaybooks($referencedPlaybooks)
  {
    $this->referencedPlaybooks = $referencedPlaybooks;
  }
  /**
   * @return string[]
   */
  public function getReferencedPlaybooks()
  {
    return $this->referencedPlaybooks;
  }
  /**
   * @param string[]
   */
  public function setReferencedTools($referencedTools)
  {
    $this->referencedTools = $referencedTools;
  }
  /**
   * @return string[]
   */
  public function getReferencedTools()
  {
    return $this->referencedTools;
  }
  /**
   * @param string
   */
  public function setTokenCount($tokenCount)
  {
    $this->tokenCount = $tokenCount;
  }
  /**
   * @return string
   */
  public function getTokenCount()
  {
    return $this->tokenCount;
  }
  /**
   * @param string
   */
  public function setUpdateTime($updateTime)
  {
    $this->updateTime = $updateTime;
  }
  /**
   * @return string
   */
  public function getUpdateTime()
  {
    return $this->updateTime;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDialogflowCxV3Playbook::class, 'Google_Service_Dialogflow_GoogleCloudDialogflowCxV3Playbook');
