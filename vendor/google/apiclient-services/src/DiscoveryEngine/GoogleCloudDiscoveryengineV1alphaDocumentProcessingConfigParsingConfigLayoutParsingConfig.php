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

class GoogleCloudDiscoveryengineV1alphaDocumentProcessingConfigParsingConfigLayoutParsingConfig extends \Google\Collection
{
  protected $collection_key = 'structuredContentTypes';
  /**
   * @var bool
   */
  public $enableImageAnnotation;
  /**
   * @var bool
   */
  public $enableTableAnnotation;
  /**
   * @var string[]
   */
  public $excludeHtmlClasses;
  /**
   * @var string[]
   */
  public $excludeHtmlElements;
  /**
   * @var string[]
   */
  public $excludeHtmlIds;
  /**
   * @var string[]
   */
  public $structuredContentTypes;

  /**
   * @param bool
   */
  public function setEnableImageAnnotation($enableImageAnnotation)
  {
    $this->enableImageAnnotation = $enableImageAnnotation;
  }
  /**
   * @return bool
   */
  public function getEnableImageAnnotation()
  {
    return $this->enableImageAnnotation;
  }
  /**
   * @param bool
   */
  public function setEnableTableAnnotation($enableTableAnnotation)
  {
    $this->enableTableAnnotation = $enableTableAnnotation;
  }
  /**
   * @return bool
   */
  public function getEnableTableAnnotation()
  {
    return $this->enableTableAnnotation;
  }
  /**
   * @param string[]
   */
  public function setExcludeHtmlClasses($excludeHtmlClasses)
  {
    $this->excludeHtmlClasses = $excludeHtmlClasses;
  }
  /**
   * @return string[]
   */
  public function getExcludeHtmlClasses()
  {
    return $this->excludeHtmlClasses;
  }
  /**
   * @param string[]
   */
  public function setExcludeHtmlElements($excludeHtmlElements)
  {
    $this->excludeHtmlElements = $excludeHtmlElements;
  }
  /**
   * @return string[]
   */
  public function getExcludeHtmlElements()
  {
    return $this->excludeHtmlElements;
  }
  /**
   * @param string[]
   */
  public function setExcludeHtmlIds($excludeHtmlIds)
  {
    $this->excludeHtmlIds = $excludeHtmlIds;
  }
  /**
   * @return string[]
   */
  public function getExcludeHtmlIds()
  {
    return $this->excludeHtmlIds;
  }
  /**
   * @param string[]
   */
  public function setStructuredContentTypes($structuredContentTypes)
  {
    $this->structuredContentTypes = $structuredContentTypes;
  }
  /**
   * @return string[]
   */
  public function getStructuredContentTypes()
  {
    return $this->structuredContentTypes;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDiscoveryengineV1alphaDocumentProcessingConfigParsingConfigLayoutParsingConfig::class, 'Google_Service_DiscoveryEngine_GoogleCloudDiscoveryengineV1alphaDocumentProcessingConfigParsingConfigLayoutParsingConfig');
