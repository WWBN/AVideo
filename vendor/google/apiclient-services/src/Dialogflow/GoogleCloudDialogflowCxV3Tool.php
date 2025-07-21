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

class GoogleCloudDialogflowCxV3Tool extends \Google\Model
{
  protected $dataStoreSpecType = GoogleCloudDialogflowCxV3ToolDataStoreTool::class;
  protected $dataStoreSpecDataType = '';
  /**
   * @var string
   */
  public $description;
  /**
   * @var string
   */
  public $displayName;
  protected $functionSpecType = GoogleCloudDialogflowCxV3ToolFunctionTool::class;
  protected $functionSpecDataType = '';
  /**
   * @var string
   */
  public $name;
  protected $openApiSpecType = GoogleCloudDialogflowCxV3ToolOpenApiTool::class;
  protected $openApiSpecDataType = '';
  /**
   * @var string
   */
  public $toolType;

  /**
   * @param GoogleCloudDialogflowCxV3ToolDataStoreTool
   */
  public function setDataStoreSpec(GoogleCloudDialogflowCxV3ToolDataStoreTool $dataStoreSpec)
  {
    $this->dataStoreSpec = $dataStoreSpec;
  }
  /**
   * @return GoogleCloudDialogflowCxV3ToolDataStoreTool
   */
  public function getDataStoreSpec()
  {
    return $this->dataStoreSpec;
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
   * @param GoogleCloudDialogflowCxV3ToolFunctionTool
   */
  public function setFunctionSpec(GoogleCloudDialogflowCxV3ToolFunctionTool $functionSpec)
  {
    $this->functionSpec = $functionSpec;
  }
  /**
   * @return GoogleCloudDialogflowCxV3ToolFunctionTool
   */
  public function getFunctionSpec()
  {
    return $this->functionSpec;
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
   * @param GoogleCloudDialogflowCxV3ToolOpenApiTool
   */
  public function setOpenApiSpec(GoogleCloudDialogflowCxV3ToolOpenApiTool $openApiSpec)
  {
    $this->openApiSpec = $openApiSpec;
  }
  /**
   * @return GoogleCloudDialogflowCxV3ToolOpenApiTool
   */
  public function getOpenApiSpec()
  {
    return $this->openApiSpec;
  }
  /**
   * @param string
   */
  public function setToolType($toolType)
  {
    $this->toolType = $toolType;
  }
  /**
   * @return string
   */
  public function getToolType()
  {
    return $this->toolType;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDialogflowCxV3Tool::class, 'Google_Service_Dialogflow_GoogleCloudDialogflowCxV3Tool');
