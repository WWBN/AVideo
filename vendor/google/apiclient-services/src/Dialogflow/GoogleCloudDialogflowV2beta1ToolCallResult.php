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

class GoogleCloudDialogflowV2beta1ToolCallResult extends \Google\Model
{
  /**
   * @var string
   */
  public $action;
  /**
   * @var string
   */
  public $content;
  /**
   * @var string
   */
  public $createTime;
  protected $errorType = GoogleCloudDialogflowV2beta1ToolCallResultError::class;
  protected $errorDataType = '';
  /**
   * @var string
   */
  public $rawContent;
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
   * @param string
   */
  public function setContent($content)
  {
    $this->content = $content;
  }
  /**
   * @return string
   */
  public function getContent()
  {
    return $this->content;
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
   * @param GoogleCloudDialogflowV2beta1ToolCallResultError
   */
  public function setError(GoogleCloudDialogflowV2beta1ToolCallResultError $error)
  {
    $this->error = $error;
  }
  /**
   * @return GoogleCloudDialogflowV2beta1ToolCallResultError
   */
  public function getError()
  {
    return $this->error;
  }
  /**
   * @param string
   */
  public function setRawContent($rawContent)
  {
    $this->rawContent = $rawContent;
  }
  /**
   * @return string
   */
  public function getRawContent()
  {
    return $this->rawContent;
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
class_alias(GoogleCloudDialogflowV2beta1ToolCallResult::class, 'Google_Service_Dialogflow_GoogleCloudDialogflowV2beta1ToolCallResult');
