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

class GoogleCloudDialogflowCxV3Example extends \Google\Collection
{
  protected $collection_key = 'actions';
  protected $actionsType = GoogleCloudDialogflowCxV3Action::class;
  protected $actionsDataType = 'array';
  /**
   * @var string
   */
  public $conversationState;
  /**
   * @var string
   */
  public $createTime;
  /**
   * @var string
   */
  public $description;
  /**
   * @var string
   */
  public $displayName;
  /**
   * @var string
   */
  public $languageCode;
  /**
   * @var string
   */
  public $name;
  protected $playbookInputType = GoogleCloudDialogflowCxV3PlaybookInput::class;
  protected $playbookInputDataType = '';
  protected $playbookOutputType = GoogleCloudDialogflowCxV3PlaybookOutput::class;
  protected $playbookOutputDataType = '';
  /**
   * @var string
   */
  public $tokenCount;
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param GoogleCloudDialogflowCxV3Action[]
   */
  public function setActions($actions)
  {
    $this->actions = $actions;
  }
  /**
   * @return GoogleCloudDialogflowCxV3Action[]
   */
  public function getActions()
  {
    return $this->actions;
  }
  /**
   * @param string
   */
  public function setConversationState($conversationState)
  {
    $this->conversationState = $conversationState;
  }
  /**
   * @return string
   */
  public function getConversationState()
  {
    return $this->conversationState;
  }
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
  public function setDescription($description)
  {
    $this->description = $description;
  }
  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
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
  public function setLanguageCode($languageCode)
  {
    $this->languageCode = $languageCode;
  }
  /**
   * @return string
   */
  public function getLanguageCode()
  {
    return $this->languageCode;
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
   * @param GoogleCloudDialogflowCxV3PlaybookInput
   */
  public function setPlaybookInput(GoogleCloudDialogflowCxV3PlaybookInput $playbookInput)
  {
    $this->playbookInput = $playbookInput;
  }
  /**
   * @return GoogleCloudDialogflowCxV3PlaybookInput
   */
  public function getPlaybookInput()
  {
    return $this->playbookInput;
  }
  /**
   * @param GoogleCloudDialogflowCxV3PlaybookOutput
   */
  public function setPlaybookOutput(GoogleCloudDialogflowCxV3PlaybookOutput $playbookOutput)
  {
    $this->playbookOutput = $playbookOutput;
  }
  /**
   * @return GoogleCloudDialogflowCxV3PlaybookOutput
   */
  public function getPlaybookOutput()
  {
    return $this->playbookOutput;
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
class_alias(GoogleCloudDialogflowCxV3Example::class, 'Google_Service_Dialogflow_GoogleCloudDialogflowCxV3Example');
