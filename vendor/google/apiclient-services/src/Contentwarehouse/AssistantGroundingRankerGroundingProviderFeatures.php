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

namespace Google\Service\Contentwarehouse;

class AssistantGroundingRankerGroundingProviderFeatures extends \Google\Model
{
  protected $contactGroundingProviderFeaturesType = AssistantGroundingRankerContactGroundingProviderFeatures::class;
  protected $contactGroundingProviderFeaturesDataType = '';
  protected $deviceGroundingProviderFeaturesType = AssistantGroundingRankerDeviceGroundingProviderFeatures::class;
  protected $deviceGroundingProviderFeaturesDataType = '';
  protected $mediaGroundingProviderFeaturesType = AssistantGroundingRankerMediaGroundingProviderFeatures::class;
  protected $mediaGroundingProviderFeaturesDataType = '';
  protected $podcastGroundingProviderFeaturesType = AssistantGroundingRankerPodcastGroundingProviderFeatures::class;
  protected $podcastGroundingProviderFeaturesDataType = '';
  protected $providerGroundingProviderFeaturesType = AssistantGroundingRankerProviderGroundingProviderFeatures::class;
  protected $providerGroundingProviderFeaturesDataType = '';

  /**
   * @param AssistantGroundingRankerContactGroundingProviderFeatures
   */
  public function setContactGroundingProviderFeatures(AssistantGroundingRankerContactGroundingProviderFeatures $contactGroundingProviderFeatures)
  {
    $this->contactGroundingProviderFeatures = $contactGroundingProviderFeatures;
  }
  /**
   * @return AssistantGroundingRankerContactGroundingProviderFeatures
   */
  public function getContactGroundingProviderFeatures()
  {
    return $this->contactGroundingProviderFeatures;
  }
  /**
   * @param AssistantGroundingRankerDeviceGroundingProviderFeatures
   */
  public function setDeviceGroundingProviderFeatures(AssistantGroundingRankerDeviceGroundingProviderFeatures $deviceGroundingProviderFeatures)
  {
    $this->deviceGroundingProviderFeatures = $deviceGroundingProviderFeatures;
  }
  /**
   * @return AssistantGroundingRankerDeviceGroundingProviderFeatures
   */
  public function getDeviceGroundingProviderFeatures()
  {
    return $this->deviceGroundingProviderFeatures;
  }
  /**
   * @param AssistantGroundingRankerMediaGroundingProviderFeatures
   */
  public function setMediaGroundingProviderFeatures(AssistantGroundingRankerMediaGroundingProviderFeatures $mediaGroundingProviderFeatures)
  {
    $this->mediaGroundingProviderFeatures = $mediaGroundingProviderFeatures;
  }
  /**
   * @return AssistantGroundingRankerMediaGroundingProviderFeatures
   */
  public function getMediaGroundingProviderFeatures()
  {
    return $this->mediaGroundingProviderFeatures;
  }
  /**
   * @param AssistantGroundingRankerPodcastGroundingProviderFeatures
   */
  public function setPodcastGroundingProviderFeatures(AssistantGroundingRankerPodcastGroundingProviderFeatures $podcastGroundingProviderFeatures)
  {
    $this->podcastGroundingProviderFeatures = $podcastGroundingProviderFeatures;
  }
  /**
   * @return AssistantGroundingRankerPodcastGroundingProviderFeatures
   */
  public function getPodcastGroundingProviderFeatures()
  {
    return $this->podcastGroundingProviderFeatures;
  }
  /**
   * @param AssistantGroundingRankerProviderGroundingProviderFeatures
   */
  public function setProviderGroundingProviderFeatures(AssistantGroundingRankerProviderGroundingProviderFeatures $providerGroundingProviderFeatures)
  {
    $this->providerGroundingProviderFeatures = $providerGroundingProviderFeatures;
  }
  /**
   * @return AssistantGroundingRankerProviderGroundingProviderFeatures
   */
  public function getProviderGroundingProviderFeatures()
  {
    return $this->providerGroundingProviderFeatures;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantGroundingRankerGroundingProviderFeatures::class, 'Google_Service_Contentwarehouse_AssistantGroundingRankerGroundingProviderFeatures');
