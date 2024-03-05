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

namespace Google\Service\DiscoveryEngine;

class GoogleCloudDiscoveryengineV1alphaFieldConfig extends \Google\Model
{
  /**
   * @var string
   */
  public $completableOption;
  /**
   * @var string
   */
  public $dynamicFacetableOption;
  /**
   * @var string
   */
  public $fieldPath;
  /**
   * @var string
   */
  public $fieldType;
  /**
   * @var string
   */
  public $indexableOption;
  /**
   * @var string
   */
  public $keyPropertyType;
  /**
   * @var string
   */
  public $recsFilterableOption;
  /**
   * @var string
   */
  public $retrievableOption;
  /**
   * @var string
   */
  public $searchableOption;

  /**
   * @param string
   */
  public function setCompletableOption($completableOption)
  {
    $this->completableOption = $completableOption;
  }
  /**
   * @return string
   */
  public function getCompletableOption()
  {
    return $this->completableOption;
  }
  /**
   * @param string
   */
  public function setDynamicFacetableOption($dynamicFacetableOption)
  {
    $this->dynamicFacetableOption = $dynamicFacetableOption;
  }
  /**
   * @return string
   */
  public function getDynamicFacetableOption()
  {
    return $this->dynamicFacetableOption;
  }
  /**
   * @param string
   */
  public function setFieldPath($fieldPath)
  {
    $this->fieldPath = $fieldPath;
  }
  /**
   * @return string
   */
  public function getFieldPath()
  {
    return $this->fieldPath;
  }
  /**
   * @param string
   */
  public function setFieldType($fieldType)
  {
    $this->fieldType = $fieldType;
  }
  /**
   * @return string
   */
  public function getFieldType()
  {
    return $this->fieldType;
  }
  /**
   * @param string
   */
  public function setIndexableOption($indexableOption)
  {
    $this->indexableOption = $indexableOption;
  }
  /**
   * @return string
   */
  public function getIndexableOption()
  {
    return $this->indexableOption;
  }
  /**
   * @param string
   */
  public function setKeyPropertyType($keyPropertyType)
  {
    $this->keyPropertyType = $keyPropertyType;
  }
  /**
   * @return string
   */
  public function getKeyPropertyType()
  {
    return $this->keyPropertyType;
  }
  /**
   * @param string
   */
  public function setRecsFilterableOption($recsFilterableOption)
  {
    $this->recsFilterableOption = $recsFilterableOption;
  }
  /**
   * @return string
   */
  public function getRecsFilterableOption()
  {
    return $this->recsFilterableOption;
  }
  /**
   * @param string
   */
  public function setRetrievableOption($retrievableOption)
  {
    $this->retrievableOption = $retrievableOption;
  }
  /**
   * @return string
   */
  public function getRetrievableOption()
  {
    return $this->retrievableOption;
  }
  /**
   * @param string
   */
  public function setSearchableOption($searchableOption)
  {
    $this->searchableOption = $searchableOption;
  }
  /**
   * @return string
   */
  public function getSearchableOption()
  {
    return $this->searchableOption;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDiscoveryengineV1alphaFieldConfig::class, 'Google_Service_DiscoveryEngine_GoogleCloudDiscoveryengineV1alphaFieldConfig');
