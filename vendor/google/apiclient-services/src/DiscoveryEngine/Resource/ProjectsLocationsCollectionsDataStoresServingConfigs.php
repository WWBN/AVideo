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

namespace Google\Service\DiscoveryEngine\Resource;

use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaRecommendRequest;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaRecommendResponse;

/**
 * The "servingConfigs" collection of methods.
 * Typical usage is:
 *  <code>
 *   $discoveryengineService = new Google\Service\DiscoveryEngine(...);
 *   $servingConfigs = $discoveryengineService->projects_locations_collections_dataStores_servingConfigs;
 *  </code>
 */
class ProjectsLocationsCollectionsDataStoresServingConfigs extends \Google\Service\Resource
{
  /**
   * Makes a recommendation, which requires a contextual user event.
   * (servingConfigs.recommend)
   *
   * @param string $servingConfig Required. Full resource name of the format:
   * `projects/locations/global/collections/dataStores/servingConfigs` Before you
   * can request recommendations from your model, you must create at least one
   * serving config for it.
   * @param GoogleCloudDiscoveryengineV1betaRecommendRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleCloudDiscoveryengineV1betaRecommendResponse
   */
  public function recommend($servingConfig, GoogleCloudDiscoveryengineV1betaRecommendRequest $postBody, $optParams = [])
  {
    $params = ['servingConfig' => $servingConfig, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('recommend', [$params], GoogleCloudDiscoveryengineV1betaRecommendResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsCollectionsDataStoresServingConfigs::class, 'Google_Service_DiscoveryEngine_Resource_ProjectsLocationsCollectionsDataStoresServingConfigs');
