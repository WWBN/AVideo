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

class GoogleCloudApihubV1Attribute extends \Google\Collection
{
  protected $collection_key = 'allowedValues';
  protected $allowedValuesType = GoogleCloudApihubV1AllowedValue::class;
  protected $allowedValuesDataType = 'array';
  /**
   * @var int
   */
  public $cardinality;
  /**
   * @var string
   */
  public $createTime;
  /**
   * @var string
   */
  public $dataType;
  /**
   * @var string
   */
  public $definitionType;
  /**
   * @var string
   */
  public $description;
  /**
   * @var string
   */
  public $displayName;
  /**
   * @var bool
   */
  public $mandatory;
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $scope;
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param GoogleCloudApihubV1AllowedValue[]
   */
  public function setAllowedValues($allowedValues)
  {
    $this->allowedValues = $allowedValues;
  }
  /**
   * @return GoogleCloudApihubV1AllowedValue[]
   */
  public function getAllowedValues()
  {
    return $this->allowedValues;
  }
  /**
   * @param int
   */
  public function setCardinality($cardinality)
  {
    $this->cardinality = $cardinality;
  }
  /**
   * @return int
   */
  public function getCardinality()
  {
    return $this->cardinality;
  }
  /**
   * @param string
   */
  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  /**
   * @return string
   */
  public function getCreateTime()
  {
    return $this->createTime;
  }
  /**
   * @param string
   */
  public function setDataType($dataType)
  {
    $this->dataType = $dataType;
  }
  /**
   * @return string
   */
  public function getDataType()
  {
    return $this->dataType;
  }
  /**
   * @param string
   */
  public function setDefinitionType($definitionType)
  {
    $this->definitionType = $definitionType;
  }
  /**
   * @return string
   */
  public function getDefinitionType()
  {
    return $this->definitionType;
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
   * @param bool
   */
  public function setMandatory($mandatory)
  {
    $this->mandatory = $mandatory;
  }
  /**
   * @return bool
   */
  public function getMandatory()
  {
    return $this->mandatory;
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
   * @param string
   */
  public function setScope($scope)
  {
    $this->scope = $scope;
  }
  /**
   * @return string
   */
  public function getScope()
  {
    return $this->scope;
  }
  /**
   * @param string
   */
  public function setUpdateTime($updateTime)
  {
    $this->updateTime = $updateTime;
  }
  /**
   * @return string
   */
  public function getUpdateTime()
  {
    return $this->updateTime;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1Attribute::class, 'Google_Service_APIhub_GoogleCloudApihubV1Attribute');
