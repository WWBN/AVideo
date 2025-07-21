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

namespace Google\Service\Contactcenterinsights;

class GoogleCloudContactcenterinsightsV1SampleRule extends \Google\Model
{
  /**
   * @var string
   */
  public $conversationFilter;
  /**
   * @var string
   */
  public $dimension;
  public $samplePercentage;
  /**
   * @var string
   */
  public $sampleRow;

  /**
   * @param string
   */
  public function setConversationFilter($conversationFilter)
  {
    $this->conversationFilter = $conversationFilter;
  }
  /**
   * @return string
   */
  public function getConversationFilter()
  {
    return $this->conversationFilter;
  }
  /**
   * @param string
   */
  public function setDimension($dimension)
  {
    $this->dimension = $dimension;
  }
  /**
   * @return string
   */
  public function getDimension()
  {
    return $this->dimension;
  }
  public function setSamplePercentage($samplePercentage)
  {
    $this->samplePercentage = $samplePercentage;
  }
  public function getSamplePercentage()
  {
    return $this->samplePercentage;
  }
  /**
   * @param string
   */
  public function setSampleRow($sampleRow)
  {
    $this->sampleRow = $sampleRow;
  }
  /**
   * @return string
   */
  public function getSampleRow()
  {
    return $this->sampleRow;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudContactcenterinsightsV1SampleRule::class, 'Google_Service_Contactcenterinsights_GoogleCloudContactcenterinsightsV1SampleRule');
