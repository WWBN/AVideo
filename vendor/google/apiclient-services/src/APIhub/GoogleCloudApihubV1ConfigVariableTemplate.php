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

class GoogleCloudApihubV1ConfigVariableTemplate extends \Google\Collection
{
  protected $collection_key = 'multiSelectOptions';
  /**
   * @var string
   */
  public $description;
  protected $enumOptionsType = GoogleCloudApihubV1ConfigValueOption::class;
  protected $enumOptionsDataType = 'array';
  /**
   * @var string
   */
  public $id;
  protected $multiSelectOptionsType = GoogleCloudApihubV1ConfigValueOption::class;
  protected $multiSelectOptionsDataType = 'array';
  /**
   * @var bool
   */
  public $required;
  /**
   * @var string
   */
  public $validationRegex;
  /**
   * @var string
   */
  public $valueType;

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
   * @param GoogleCloudApihubV1ConfigValueOption[]
   */
  public function setEnumOptions($enumOptions)
  {
    $this->enumOptions = $enumOptions;
  }
  /**
   * @return GoogleCloudApihubV1ConfigValueOption[]
   */
  public function getEnumOptions()
  {
    return $this->enumOptions;
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
   * @param GoogleCloudApihubV1ConfigValueOption[]
   */
  public function setMultiSelectOptions($multiSelectOptions)
  {
    $this->multiSelectOptions = $multiSelectOptions;
  }
  /**
   * @return GoogleCloudApihubV1ConfigValueOption[]
   */
  public function getMultiSelectOptions()
  {
    return $this->multiSelectOptions;
  }
  /**
   * @param bool
   */
  public function setRequired($required)
  {
    $this->required = $required;
  }
  /**
   * @return bool
   */
  public function getRequired()
  {
    return $this->required;
  }
  /**
   * @param string
   */
  public function setValidationRegex($validationRegex)
  {
    $this->validationRegex = $validationRegex;
  }
  /**
   * @return string
   */
  public function getValidationRegex()
  {
    return $this->validationRegex;
  }
  /**
   * @param string
   */
  public function setValueType($valueType)
  {
    $this->valueType = $valueType;
  }
  /**
   * @return string
   */
  public function getValueType()
  {
    return $this->valueType;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1ConfigVariableTemplate::class, 'Google_Service_APIhub_GoogleCloudApihubV1ConfigVariableTemplate');
