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

class GoogleCloudDiscoveryengineV1alphaDocumentProcessingConfigParsingConfig extends \Google\Model
{
  protected $digitalParsingConfigType = GoogleCloudDiscoveryengineV1alphaDigitalParsingConfig::class;
  protected $digitalParsingConfigDataType = '';
  protected $layoutParsingConfigType = GoogleCloudDiscoveryengineV1alphaLayoutParsingConfig::class;
  protected $layoutParsingConfigDataType = '';
  protected $ocrParsingConfigType = GoogleCloudDiscoveryengineV1alphaOcrParsingConfig::class;
  protected $ocrParsingConfigDataType = '';

  /**
   * @param GoogleCloudDiscoveryengineV1alphaDigitalParsingConfig
   */
  public function setDigitalParsingConfig(GoogleCloudDiscoveryengineV1alphaDigitalParsingConfig $digitalParsingConfig)
  {
    $this->digitalParsingConfig = $digitalParsingConfig;
  }
  /**
   * @return GoogleCloudDiscoveryengineV1alphaDigitalParsingConfig
   */
  public function getDigitalParsingConfig()
  {
    return $this->digitalParsingConfig;
  }
  /**
   * @param GoogleCloudDiscoveryengineV1alphaLayoutParsingConfig
   */
  public function setLayoutParsingConfig(GoogleCloudDiscoveryengineV1alphaLayoutParsingConfig $layoutParsingConfig)
  {
    $this->layoutParsingConfig = $layoutParsingConfig;
  }
  /**
   * @return GoogleCloudDiscoveryengineV1alphaLayoutParsingConfig
   */
  public function getLayoutParsingConfig()
  {
    return $this->layoutParsingConfig;
  }
  /**
   * @param GoogleCloudDiscoveryengineV1alphaOcrParsingConfig
   */
  public function setOcrParsingConfig(GoogleCloudDiscoveryengineV1alphaOcrParsingConfig $ocrParsingConfig)
  {
    $this->ocrParsingConfig = $ocrParsingConfig;
  }
  /**
   * @return GoogleCloudDiscoveryengineV1alphaOcrParsingConfig
   */
  public function getOcrParsingConfig()
  {
    return $this->ocrParsingConfig;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDiscoveryengineV1alphaDocumentProcessingConfigParsingConfig::class, 'Google_Service_DiscoveryEngine_GoogleCloudDiscoveryengineV1alphaDocumentProcessingConfigParsingConfig');
