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

namespace Google\Service\CloudAlloyDBAdmin;

class SupportedDatabaseFlag extends \Google\Collection
{
  protected $collection_key = 'supportedDbVersions';
  /**
   * @var bool
   */
  public $acceptsMultipleValues;
  /**
   * @var string
   */
  public $flagName;
  protected $integerRestrictionsType = IntegerRestrictions::class;
  protected $integerRestrictionsDataType = '';
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $recommendedIntegerValue;
  /**
   * @var string
   */
  public $recommendedStringValue;
  /**
   * @var bool
   */
  public $requiresDbRestart;
  /**
   * @var string
   */
  public $scope;
  protected $stringRestrictionsType = StringRestrictions::class;
  protected $stringRestrictionsDataType = '';
  /**
   * @var string[]
   */
  public $supportedDbVersions;
  /**
   * @var string
   */
  public $valueType;

  /**
   * @param bool
   */
  public function setAcceptsMultipleValues($acceptsMultipleValues)
  {
    $this->acceptsMultipleValues = $acceptsMultipleValues;
  }
  /**
   * @return bool
   */
  public function getAcceptsMultipleValues()
  {
    return $this->acceptsMultipleValues;
  }
  /**
   * @param string
   */
  public function setFlagName($flagName)
  {
    $this->flagName = $flagName;
  }
  /**
   * @return string
   */
  public function getFlagName()
  {
    return $this->flagName;
  }
  /**
   * @param IntegerRestrictions
   */
  public function setIntegerRestrictions(IntegerRestrictions $integerRestrictions)
  {
    $this->integerRestrictions = $integerRestrictions;
  }
  /**
   * @return IntegerRestrictions
   */
  public function getIntegerRestrictions()
  {
    return $this->integerRestrictions;
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
  public function setRecommendedIntegerValue($recommendedIntegerValue)
  {
    $this->recommendedIntegerValue = $recommendedIntegerValue;
  }
  /**
   * @return string
   */
  public function getRecommendedIntegerValue()
  {
    return $this->recommendedIntegerValue;
  }
  /**
   * @param string
   */
  public function setRecommendedStringValue($recommendedStringValue)
  {
    $this->recommendedStringValue = $recommendedStringValue;
  }
  /**
   * @return string
   */
  public function getRecommendedStringValue()
  {
    return $this->recommendedStringValue;
  }
  /**
   * @param bool
   */
  public function setRequiresDbRestart($requiresDbRestart)
  {
    $this->requiresDbRestart = $requiresDbRestart;
  }
  /**
   * @return bool
   */
  public function getRequiresDbRestart()
  {
    return $this->requiresDbRestart;
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
   * @param StringRestrictions
   */
  public function setStringRestrictions(StringRestrictions $stringRestrictions)
  {
    $this->stringRestrictions = $stringRestrictions;
  }
  /**
   * @return StringRestrictions
   */
  public function getStringRestrictions()
  {
    return $this->stringRestrictions;
  }
  /**
   * @param string[]
   */
  public function setSupportedDbVersions($supportedDbVersions)
  {
    $this->supportedDbVersions = $supportedDbVersions;
  }
  /**
   * @return string[]
   */
  public function getSupportedDbVersions()
  {
    return $this->supportedDbVersions;
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
class_alias(SupportedDatabaseFlag::class, 'Google_Service_CloudAlloyDBAdmin_SupportedDatabaseFlag');
