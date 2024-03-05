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

use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaListServingConfigsResponse;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaRecommendRequest;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaRecommendResponse;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaSearchRequest;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaSearchResponse;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaServingConfig;

/**
 * The "servingConfigs" collection of methods.
 * Typical usage is:
 *  <code>
 *   $discoveryengineService = new Google\Service\DiscoveryEngine(...);
 *   $servingConfigs = $discoveryengineService->projects_locations_dataStores_servingConfigs;
 *  </code>
 */
class ProjectsLocationsDataStoresServingConfigs extends \Google\Service\Resource
{
  /**
   * Gets a ServingConfig. Returns a NotFound error if the ServingConfig does not
   * exist. (servingConfigs.get)
   *
   * @param string $name Required. The resource name of the ServingConfig to get.
   * Format: `projects/{project_number}/locations/{location}/collections/{collecti
   * on}/dataStores/{data_store}/servingConfigs/{serving_config_id}`
   * @param array $optParams Optional parameters.
   * @return GoogleCloudDiscoveryengineV1betaServingConfig
   * @throws \Google\Service\Exception
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], GoogleCloudDiscoveryengineV1betaServingConfig::class);
  }
  /**
   * Lists all ServingConfigs linked to this dataStore.
   * (servingConfigs.listProjectsLocationsDataStoresServingConfigs)
   *
   * @param string $parent Required. The dataStore resource name. Format: `project
   * s/{project_number}/locations/{location}/collections/{collection}/dataStores/{
   * data_store}`
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Optional. Maximum number of results to return. If
   * unspecified, defaults to 100. If a value greater than 100 is provided, at
   * most 100 results are returned.
   * @opt_param string pageToken Optional. A page token, received from a previous
   * `ListServingConfigs` call. Provide this to retrieve the subsequent page.
   * @return GoogleCloudDiscoveryengineV1betaListServingConfigsResponse
   * @throws \Google\Service\Exception
   */
  public function listProjectsLocationsDataStoresServingConfigs($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], GoogleCloudDiscoveryengineV1betaListServingConfigsResponse::class);
  }
  /**
   * Updates a ServingConfig. Returns a NOT_FOUND error if the ServingConfig does
   * not exist. (servingConfigs.patch)
   *
   * @param string $name Immutable. Fully qualified name `projects/{project}/locat
   * ions/{location}/collections/{collection_id}/dataStores/{data_store_id}/servin
   * gConfigs/{serving_config_id}`
   * @param GoogleCloudDiscoveryengineV1betaServingConfig $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask Indicates which fields in the provided
   * ServingConfig to update. The following are NOT supported: *
   * ServingConfig.name If not set, all supported fields are updated.
   * @return GoogleCloudDiscoveryengineV1betaServingConfig
   * @throws \Google\Service\Exception
   */
  public function patch($name, GoogleCloudDiscoveryengineV1betaServingConfig $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], GoogleCloudDiscoveryengineV1betaServingConfig::class);
  }
  /**
   * Makes a recommendation, which requires a contextual user event.
   * (servingConfigs.recommend)
   *
   * @param string $servingConfig Required. Full resource name of a ServingConfig:
   * `projects/locations/global/collections/engines/servingConfigs`, or
   * `projects/locations/global/collections/dataStores/servingConfigs` One default
   * serving config is created along with your recommendation engine creation. The
   * engine ID will be used as the ID of the default serving config. For example,
   * for Engine `projects/locations/global/collections/engines/my-engine`, you can
   * use `projects/locations/global/collections/engines/my-
   * engine/servingConfigs/my-engine` for your RecommendationService.Recommend
   * requests.
   * @param GoogleCloudDiscoveryengineV1betaRecommendRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleCloudDiscoveryengineV1betaRecommendResponse
   * @throws \Google\Service\Exception
   */
  public function recommend($servingConfig, GoogleCloudDiscoveryengineV1betaRecommendRequest $postBody, $optParams = [])
  {
    $params = ['servingConfig' => $servingConfig, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('recommend', [$params], GoogleCloudDiscoveryengineV1betaRecommendResponse::class);
  }
  /**
   * Performs a search. (servingConfigs.search)
   *
   * @param string $servingConfig Required. The resource name of the Search
   * serving config, such as `projects/locations/global/collections/default_collec
   * tion/engines/servingConfigs/default_serving_config`, or `projects/locations/g
   * lobal/collections/default_collection/dataStores/default_data_store/servingCon
   * figs/default_serving_config`. This field is used to identify the serving
   * configuration name, set of models used to make the search.
   * @param GoogleCloudDiscoveryengineV1betaSearchRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleCloudDiscoveryengineV1betaSearchResponse
   * @throws \Google\Service\Exception
   */
  public function search($servingConfig, GoogleCloudDiscoveryengineV1betaSearchRequest $postBody, $optParams = [])
  {
    $params = ['servingConfig' => $servingConfig, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('search', [$params], GoogleCloudDiscoveryengineV1betaSearchResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsDataStoresServingConfigs::class, 'Google_Service_DiscoveryEngine_Resource_ProjectsLocationsDataStoresServingConfigs');
