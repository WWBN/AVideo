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

class GoogleCloudApihubV1ConfigVariable extends \Google\Model
{
  /**
   * @var bool
   */
  public $boolValue;
  protected $enumValueType = GoogleCloudApihubV1ConfigValueOption::class;
  protected $enumValueDataType = '';
  /**
   * @var string
   */
  public $intValue;
  /**
   * @var string
   */
  public $key;
  protected $multiIntValuesType = GoogleCloudApihubV1MultiIntValues::class;
  protected $multiIntValuesDataType = '';
  protected $multiSelectValuesType = GoogleCloudApihubV1MultiSelectValues::class;
  protected $multiSelectValuesDataType = '';
  protected $multiStringValuesType = GoogleCloudApihubV1MultiStringValues::class;
  protected $multiStringValuesDataType = '';
  protected $secretValueType = GoogleCloudApihubV1Secret::class;
  protected $secretValueDataType = '';
  /**
   * @var string
   */
  public $stringValue;

  /**
   * @param bool
   */
  public function setBoolValue($boolValue)
  {
    $this->boolValue = $boolValue;
  }
  /**
   * @return bool
   */
  public function getBoolValue()
  {
    return $this->boolValue;
  }
  /**
   * @param GoogleCloudApihubV1ConfigValueOption
   */
  public function setEnumValue(GoogleCloudApihubV1ConfigValueOption $enumValue)
  {
    $this->enumValue = $enumValue;
  }
  /**
   * @return GoogleCloudApihubV1ConfigValueOption
   */
  public function getEnumValue()
  {
    return $this->enumValue;
  }
  /**
   * @param string
   */
  public function setIntValue($intValue)
  {
    $this->intValue = $intValue;
  }
  /**
   * @return string
   */
  public function getIntValue()
  {
    return $this->intValue;
  }
  /**
   * @param string
   */
  public function setKey($key)
  {
    $this->key = $key;
  }
  /**
   * @return string
   */
  public function getKey()
  {
    return $this->key;
  }
  /**
   * @param GoogleCloudApihubV1MultiIntValues
   */
  public function setMultiIntValues(GoogleCloudApihubV1MultiIntValues $multiIntValues)
  {
    $this->multiIntValues = $multiIntValues;
  }
  /**
   * @return GoogleCloudApihubV1MultiIntValues
   */
  public function getMultiIntValues()
  {
    return $this->multiIntValues;
  }
  /**
   * @param GoogleCloudApihubV1MultiSelectValues
   */
  public function setMultiSelectValues(GoogleCloudApihubV1MultiSelectValues $multiSelectValues)
  {
    $this->multiSelectValues = $multiSelectValues;
  }
  /**
   * @return GoogleCloudApihubV1MultiSelectValues
   */
  public function getMultiSelectValues()
  {
    return $this->multiSelectValues;
  }
  /**
   * @param GoogleCloudApihubV1MultiStringValues
   */
  public function setMultiStringValues(GoogleCloudApihubV1MultiStringValues $multiStringValues)
  {
    $this->multiStringValues = $multiStringValues;
  }
  /**
   * @return GoogleCloudApihubV1MultiStringValues
   */
  public function getMultiStringValues()
  {
    return $this->multiStringValues;
  }
  /**
   * @param GoogleCloudApihubV1Secret
   */
  public function setSecretValue(GoogleCloudApihubV1Secret $secretValue)
  {
    $this->secretValue = $secretValue;
  }
  /**
   * @return GoogleCloudApihubV1Secret
   */
  public function getSecretValue()
  {
    return $this->secretValue;
  }
  /**
   * @param string
   */
  public function setStringValue($stringValue)
  {
    $this->stringValue = $stringValue;
  }
  /**
   * @return string
   */
  public function getStringValue()
  {
    return $this->stringValue;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1ConfigVariable::class, 'Google_Service_APIhub_GoogleCloudApihubV1ConfigVariable');
