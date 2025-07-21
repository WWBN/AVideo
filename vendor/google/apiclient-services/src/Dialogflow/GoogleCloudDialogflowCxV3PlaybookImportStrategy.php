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

class GoogleCloudDialogflowCxV3PlaybookImportStrategy extends \Google\Model
{
  /**
   * @var string
   */
  public $mainPlaybookImportStrategy;
  /**
   * @var string
   */
  public $nestedResourceImportStrategy;
  /**
   * @var string
   */
  public $toolImportStrategy;

  /**
   * @param string
   */
  public function setMainPlaybookImportStrategy($mainPlaybookImportStrategy)
  {
    $this->mainPlaybookImportStrategy = $mainPlaybookImportStrategy;
  }
  /**
   * @return string
   */
  public function getMainPlaybookImportStrategy()
  {
    return $this->mainPlaybookImportStrategy;
  }
  /**
   * @param string
   */
  public function setNestedResourceImportStrategy($nestedResourceImportStrategy)
  {
    $this->nestedResourceImportStrategy = $nestedResourceImportStrategy;
  }
  /**
   * @return string
   */
  public function getNestedResourceImportStrategy()
  {
    return $this->nestedResourceImportStrategy;
  }
  /**
   * @param string
   */
  public function setToolImportStrategy($toolImportStrategy)
  {
    $this->toolImportStrategy = $toolImportStrategy;
  }
  /**
   * @return string
   */
  public function getToolImportStrategy()
  {
    return $this->toolImportStrategy;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDialogflowCxV3PlaybookImportStrategy::class, 'Google_Service_Dialogflow_GoogleCloudDialogflowCxV3PlaybookImportStrategy');
