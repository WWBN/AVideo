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

class FieldValue extends \Google\Model
{
  protected $dateValueType = Date::class;
  protected $dateValueDataType = '';
  /**
   * @var string
   */
  public $displayName;
  /**
   * @var string
   */
  public $id;
  /**
   * @var string
   */
  public $integerValue;
  /**
   * @var string
   */
  public $longTextValue;
  protected $reasonType = Reason::class;
  protected $reasonDataType = '';
  protected $selectionListValueType = FieldValueSelectionListValue::class;
  protected $selectionListValueDataType = '';
  protected $selectionValueType = FieldValueSelectionValue::class;
  protected $selectionValueDataType = '';
  protected $textListValueType = FieldValueTextListValue::class;
  protected $textListValueDataType = '';
  /**
   * @var string
   */
  public $textValue;
  /**
   * @var string
   */
  public $type;
  /**
   * @var bool
   */
  public $unsetValue;
  protected $userListValueType = FieldValueUserListValue::class;
  protected $userListValueDataType = '';
  protected $userValueType = FieldValueUserValue::class;
  protected $userValueDataType = '';

  /**
   * @param Date
   */
  public function setDateValue(Date $dateValue)
  {
    $this->dateValue = $dateValue;
  }
  /**
   * @return Date
   */
  public function getDateValue()
  {
    return $this->dateValue;
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
   * @param string
   */
  public function setIntegerValue($integerValue)
  {
    $this->integerValue = $integerValue;
  }
  /**
   * @return string
   */
  public function getIntegerValue()
  {
    return $this->integerValue;
  }
  /**
   * @param string
   */
  public function setLongTextValue($longTextValue)
  {
    $this->longTextValue = $longTextValue;
  }
  /**
   * @return string
   */
  public function getLongTextValue()
  {
    return $this->longTextValue;
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
   * @param FieldValueSelectionListValue
   */
  public function setSelectionListValue(FieldValueSelectionListValue $selectionListValue)
  {
    $this->selectionListValue = $selectionListValue;
  }
  /**
   * @return FieldValueSelectionListValue
   */
  public function getSelectionListValue()
  {
    return $this->selectionListValue;
  }
  /**
   * @param FieldValueSelectionValue
   */
  public function setSelectionValue(FieldValueSelectionValue $selectionValue)
  {
    $this->selectionValue = $selectionValue;
  }
  /**
   * @return FieldValueSelectionValue
   */
  public function getSelectionValue()
  {
    return $this->selectionValue;
  }
  /**
   * @param FieldValueTextListValue
   */
  public function setTextListValue(FieldValueTextListValue $textListValue)
  {
    $this->textListValue = $textListValue;
  }
  /**
   * @return FieldValueTextListValue
   */
  public function getTextListValue()
  {
    return $this->textListValue;
  }
  /**
   * @param string
   */
  public function setTextValue($textValue)
  {
    $this->textValue = $textValue;
  }
  /**
   * @return string
   */
  public function getTextValue()
  {
    return $this->textValue;
  }
  /**
   * @param string
   */
  public function setType($type)
  {
    $this->type = $type;
  }
  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }
  /**
   * @param bool
   */
  public function setUnsetValue($unsetValue)
  {
    $this->unsetValue = $unsetValue;
  }
  /**
   * @return bool
   */
  public function getUnsetValue()
  {
    return $this->unsetValue;
  }
  /**
   * @param FieldValueUserListValue
   */
  public function setUserListValue(FieldValueUserListValue $userListValue)
  {
    $this->userListValue = $userListValue;
  }
  /**
   * @return FieldValueUserListValue
   */
  public function getUserListValue()
  {
    return $this->userListValue;
  }
  /**
   * @param FieldValueUserValue
   */
  public function setUserValue(FieldValueUserValue $userValue)
  {
    $this->userValue = $userValue;
  }
  /**
   * @return FieldValueUserValue
   */
  public function getUserValue()
  {
    return $this->userValue;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(FieldValue::class, 'Google_Service_Reports_FieldValue');
