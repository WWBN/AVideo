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

class AssistantGroundingRankerLaaFeatures extends \Google\Collection
{
  protected $collection_key = 'features';
  protected $bindingSetType = AssistantGroundingRankerLaaFeaturesBindingSet::class;
  protected $bindingSetDataType = '';
  protected $communicationEndpointType = AssistantGroundingRankerLaaFeaturesCommunicationEndpoint::class;
  protected $communicationEndpointDataType = '';
  protected $contactType = AssistantGroundingRankerLaaFeaturesContact::class;
  protected $contactDataType = '';
  protected $featuresType = AssistantGroundingRankerLaaFeature::class;
  protected $featuresDataType = 'array';
  protected $providerType = AssistantGroundingRankerLaaFeaturesProvider::class;
  protected $providerDataType = '';

  /**
   * @param AssistantGroundingRankerLaaFeaturesBindingSet
   */
  public function setBindingSet(AssistantGroundingRankerLaaFeaturesBindingSet $bindingSet)
  {
    $this->bindingSet = $bindingSet;
  }
  /**
   * @return AssistantGroundingRankerLaaFeaturesBindingSet
   */
  public function getBindingSet()
  {
    return $this->bindingSet;
  }
  /**
   * @param AssistantGroundingRankerLaaFeaturesCommunicationEndpoint
   */
  public function setCommunicationEndpoint(AssistantGroundingRankerLaaFeaturesCommunicationEndpoint $communicationEndpoint)
  {
    $this->communicationEndpoint = $communicationEndpoint;
  }
  /**
   * @return AssistantGroundingRankerLaaFeaturesCommunicationEndpoint
   */
  public function getCommunicationEndpoint()
  {
    return $this->communicationEndpoint;
  }
  /**
   * @param AssistantGroundingRankerLaaFeaturesContact
   */
  public function setContact(AssistantGroundingRankerLaaFeaturesContact $contact)
  {
    $this->contact = $contact;
  }
  /**
   * @return AssistantGroundingRankerLaaFeaturesContact
   */
  public function getContact()
  {
    return $this->contact;
  }
  /**
   * @param AssistantGroundingRankerLaaFeature[]
   */
  public function setFeatures($features)
  {
    $this->features = $features;
  }
  /**
   * @return AssistantGroundingRankerLaaFeature[]
   */
  public function getFeatures()
  {
    return $this->features;
  }
  /**
   * @param AssistantGroundingRankerLaaFeaturesProvider
   */
  public function setProvider(AssistantGroundingRankerLaaFeaturesProvider $provider)
  {
    $this->provider = $provider;
  }
  /**
   * @return AssistantGroundingRankerLaaFeaturesProvider
   */
  public function getProvider()
  {
    return $this->provider;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantGroundingRankerLaaFeatures::class, 'Google_Service_Contentwarehouse_AssistantGroundingRankerLaaFeatures');
