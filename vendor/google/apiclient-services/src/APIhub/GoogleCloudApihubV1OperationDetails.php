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

namespace Google\Service\APIhub;

class GoogleCloudApihubV1OperationDetails extends \Google\Model
{
  /**
   * @var bool
   */
  public $deprecated;
  /**
   * @var string
   */
  public $description;
  protected $documentationType = GoogleCloudApihubV1Documentation::class;
  protected $documentationDataType = '';
  protected $httpOperationType = GoogleCloudApihubV1HttpOperation::class;
  protected $httpOperationDataType = '';

  /**
   * @param bool
   */
  public function setDeprecated($deprecated)
  {
    $this->deprecated = $deprecated;
  }
  /**
   * @return bool
   */
  public function getDeprecated()
  {
    return $this->deprecated;
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
   * @param GoogleCloudApihubV1Documentation
   */
  public function setDocumentation(GoogleCloudApihubV1Documentation $documentation)
  {
    $this->documentation = $documentation;
  }
  /**
   * @return GoogleCloudApihubV1Documentation
   */
  public function getDocumentation()
  {
    return $this->documentation;
  }
  /**
   * @param GoogleCloudApihubV1HttpOperation
   */
  public function setHttpOperation(GoogleCloudApihubV1HttpOperation $httpOperation)
  {
    $this->httpOperation = $httpOperation;
  }
  /**
   * @return GoogleCloudApihubV1HttpOperation
   */
  public function getHttpOperation()
  {
    return $this->httpOperation;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1OperationDetails::class, 'Google_Service_APIhub_GoogleCloudApihubV1OperationDetails');
