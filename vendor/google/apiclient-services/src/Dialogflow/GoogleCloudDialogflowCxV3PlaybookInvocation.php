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

class GoogleCloudDialogflowCxV3PlaybookInvocation extends \Google\Model
{
  /**
   * @var string
   */
  public $displayName;
  /**
   * @var string
   */
  public $playbook;
  protected $playbookInputType = GoogleCloudDialogflowCxV3PlaybookInput::class;
  protected $playbookInputDataType = '';
  protected $playbookOutputType = GoogleCloudDialogflowCxV3PlaybookOutput::class;
  protected $playbookOutputDataType = '';
  /**
   * @var string
   */
  public $playbookState;

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
  public function setPlaybook($playbook)
  {
    $this->playbook = $playbook;
  }
  /**
   * @return string
   */
  public function getPlaybook()
  {
    return $this->playbook;
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
  public function setPlaybookState($playbookState)
  {
    $this->playbookState = $playbookState;
  }
  /**
   * @return string
   */
  public function getPlaybookState()
  {
    return $this->playbookState;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDialogflowCxV3PlaybookInvocation::class, 'Google_Service_Dialogflow_GoogleCloudDialogflowCxV3PlaybookInvocation');
