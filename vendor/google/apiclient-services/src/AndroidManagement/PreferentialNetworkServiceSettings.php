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

namespace Google\Service\AndroidManagement;

class PreferentialNetworkServiceSettings extends \Google\Collection
{
  protected $collection_key = 'preferentialNetworkServiceConfigs';
  /**
   * @var string
   */
  public $defaultPreferentialNetworkId;
  protected $preferentialNetworkServiceConfigsType = PreferentialNetworkServiceConfig::class;
  protected $preferentialNetworkServiceConfigsDataType = 'array';

  /**
   * @param string
   */
  public function setDefaultPreferentialNetworkId($defaultPreferentialNetworkId)
  {
    $this->defaultPreferentialNetworkId = $defaultPreferentialNetworkId;
  }
  /**
   * @return string
   */
  public function getDefaultPreferentialNetworkId()
  {
    return $this->defaultPreferentialNetworkId;
  }
  /**
   * @param PreferentialNetworkServiceConfig[]
   */
  public function setPreferentialNetworkServiceConfigs($preferentialNetworkServiceConfigs)
  {
    $this->preferentialNetworkServiceConfigs = $preferentialNetworkServiceConfigs;
  }
  /**
   * @return PreferentialNetworkServiceConfig[]
   */
  public function getPreferentialNetworkServiceConfigs()
  {
    return $this->preferentialNetworkServiceConfigs;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PreferentialNetworkServiceSettings::class, 'Google_Service_AndroidManagement_PreferentialNetworkServiceSettings');
