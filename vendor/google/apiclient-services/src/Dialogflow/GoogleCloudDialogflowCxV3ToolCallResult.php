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

class GoogleCloudDialogflowCxV3ToolCallResult extends \Google\Model
{
  /**
   * @var string
   */
  public $action;
  protected $errorType = GoogleCloudDialogflowCxV3ToolCallResultError::class;
  protected $errorDataType = '';
  /**
   * @var array[]
   */
  public $outputParameters;
  /**
   * @var string
   */
  public $tool;

  /**
   * @param string
   */
  public function setAction($action)
  {
    $this->action = $action;
  }
  /**
   * @return string
   */
  public function getAction()
  {
    return $this->action;
  }
  /**
   * @param GoogleCloudDialogflowCxV3ToolCallResultError
   */
  public function setError(GoogleCloudDialogflowCxV3ToolCallResultError $error)
  {
    $this->error = $error;
  }
  /**
   * @return GoogleCloudDialogflowCxV3ToolCallResultError
   */
  public function getError()
  {
    return $this->error;
  }
  /**
   * @param array[]
   */
  public function setOutputParameters($outputParameters)
  {
    $this->outputParameters = $outputParameters;
  }
  /**
   * @return array[]
   */
  public function getOutputParameters()
  {
    return $this->outputParameters;
  }
  /**
   * @param string
   */
  public function setTool($tool)
  {
    $this->tool = $tool;
  }
  /**
   * @return string
   */
  public function getTool()
  {
    return $this->tool;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDialogflowCxV3ToolCallResult::class, 'Google_Service_Dialogflow_GoogleCloudDialogflowCxV3ToolCallResult');
