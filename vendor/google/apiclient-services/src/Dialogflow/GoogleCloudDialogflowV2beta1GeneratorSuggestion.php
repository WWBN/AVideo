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

class GoogleCloudDialogflowV2beta1GeneratorSuggestion extends \Google\Collection
{
  protected $collection_key = 'toolCallInfo';
  protected $freeFormSuggestionType = GoogleCloudDialogflowV2beta1FreeFormSuggestion::class;
  protected $freeFormSuggestionDataType = '';
  protected $summarySuggestionType = GoogleCloudDialogflowV2beta1SummarySuggestion::class;
  protected $summarySuggestionDataType = '';
  protected $toolCallInfoType = GoogleCloudDialogflowV2beta1GeneratorSuggestionToolCallInfo::class;
  protected $toolCallInfoDataType = 'array';

  /**
   * @param GoogleCloudDialogflowV2beta1FreeFormSuggestion
   */
  public function setFreeFormSuggestion(GoogleCloudDialogflowV2beta1FreeFormSuggestion $freeFormSuggestion)
  {
    $this->freeFormSuggestion = $freeFormSuggestion;
  }
  /**
   * @return GoogleCloudDialogflowV2beta1FreeFormSuggestion
   */
  public function getFreeFormSuggestion()
  {
    return $this->freeFormSuggestion;
  }
  /**
   * @param GoogleCloudDialogflowV2beta1SummarySuggestion
   */
  public function setSummarySuggestion(GoogleCloudDialogflowV2beta1SummarySuggestion $summarySuggestion)
  {
    $this->summarySuggestion = $summarySuggestion;
  }
  /**
   * @return GoogleCloudDialogflowV2beta1SummarySuggestion
   */
  public function getSummarySuggestion()
  {
    return $this->summarySuggestion;
  }
  /**
   * @param GoogleCloudDialogflowV2beta1GeneratorSuggestionToolCallInfo[]
   */
  public function setToolCallInfo($toolCallInfo)
  {
    $this->toolCallInfo = $toolCallInfo;
  }
  /**
   * @return GoogleCloudDialogflowV2beta1GeneratorSuggestionToolCallInfo[]
   */
  public function getToolCallInfo()
  {
    return $this->toolCallInfo;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDialogflowV2beta1GeneratorSuggestion::class, 'Google_Service_Dialogflow_GoogleCloudDialogflowV2beta1GeneratorSuggestion');
