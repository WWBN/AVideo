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

namespace Google\Service\ServiceConsumerManagement;

class BatchingDescriptorProto extends \Google\Collection
{
  protected $collection_key = 'discriminatorFields';
  /**
   * @var string
   */
  public $batchedField;
  /**
   * @var string[]
   */
  public $discriminatorFields;
  /**
   * @var string
   */
  public $subresponseField;

  /**
   * @param string
   */
  public function setBatchedField($batchedField)
  {
    $this->batchedField = $batchedField;
  }
  /**
   * @return string
   */
  public function getBatchedField()
  {
    return $this->batchedField;
  }
  /**
   * @param string[]
   */
  public function setDiscriminatorFields($discriminatorFields)
  {
    $this->discriminatorFields = $discriminatorFields;
  }
  /**
   * @return string[]
   */
  public function getDiscriminatorFields()
  {
    return $this->discriminatorFields;
  }
  /**
   * @param string
   */
  public function setSubresponseField($subresponseField)
  {
    $this->subresponseField = $subresponseField;
  }
  /**
   * @return string
   */
  public function getSubresponseField()
  {
    return $this->subresponseField;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(BatchingDescriptorProto::class, 'Google_Service_ServiceConsumerManagement_BatchingDescriptorProto');
