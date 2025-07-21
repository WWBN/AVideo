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

namespace Google\Service\Reports;

class AppliedLabel extends \Google\Collection
{
  protected $collection_key = 'fieldValues';
  protected $fieldValuesType = FieldValue::class;
  protected $fieldValuesDataType = 'array';
  /**
   * @var string
   */
  public $id;
  protected $reasonType = Reason::class;
  protected $reasonDataType = '';
  /**
   * @var string
   */
  public $title;

  /**
   * @param FieldValue[]
   */
  public function setFieldValues($fieldValues)
  {
    $this->fieldValues = $fieldValues;
  }
  /**
   * @return FieldValue[]
   */
  public function getFieldValues()
  {
    return $this->fieldValues;
  }
  /**
   * @param string
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }
  /**
   * @param Reason
   */
  public function setReason(Reason $reason)
  {
    $this->reason = $reason;
  }
  /**
   * @return Reason
   */
  public function getReason()
  {
    return $this->reason;
  }
  /**
   * @param string
   */
  public function setTitle($title)
  {
    $this->title = $title;
  }
  /**
   * @return string
   */
  public function getTitle()
  {
    return $this->title;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AppliedLabel::class, 'Google_Service_Reports_AppliedLabel');
