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

class GoogleCloudDialogflowCxV3ImportPlaybookRequest extends \Google\Model
{
  protected $importStrategyType = GoogleCloudDialogflowCxV3PlaybookImportStrategy::class;
  protected $importStrategyDataType = '';
  /**
   * @var string
   */
  public $playbookContent;
  /**
   * @var string
   */
  public $playbookUri;

  /**
   * @param GoogleCloudDialogflowCxV3PlaybookImportStrategy
   */
  public function setImportStrategy(GoogleCloudDialogflowCxV3PlaybookImportStrategy $importStrategy)
  {
    $this->importStrategy = $importStrategy;
  }
  /**
   * @return GoogleCloudDialogflowCxV3PlaybookImportStrategy
   */
  public function getImportStrategy()
  {
    return $this->importStrategy;
  }
  /**
   * @param string
   */
  public function setPlaybookContent($playbookContent)
  {
    $this->playbookContent = $playbookContent;
  }
  /**
   * @return string
   */
  public function getPlaybookContent()
  {
    return $this->playbookContent;
  }
  /**
   * @param string
   */
  public function setPlaybookUri($playbookUri)
  {
    $this->playbookUri = $playbookUri;
  }
  /**
   * @return string
   */
  public function getPlaybookUri()
  {
    return $this->playbookUri;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDialogflowCxV3ImportPlaybookRequest::class, 'Google_Service_Dialogflow_GoogleCloudDialogflowCxV3ImportPlaybookRequest');
