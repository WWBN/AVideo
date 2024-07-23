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

use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1AnswerQueryRequest;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1AnswerQueryResponse;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1RecommendRequest;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1RecommendResponse;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1SearchRequest;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1SearchResponse;

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
   * Answer query method. (servingConfigs.answer)
   *
   * @param string $servingConfig Required. The resource name of the Search
   * serving config, such as `projects/locations/global/collections/default_collec
   * tion/engines/servingConfigs/default_serving_config`, or `projects/locations/g
   * lobal/collections/default_collection/dataStores/servingConfigs/default_servin
   * g_config`. This field is used to identify the serving configuration name, set
   * of models used to make the search.
   * @param GoogleCloudDiscoveryengineV1AnswerQueryRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleCloudDiscoveryengineV1AnswerQueryResponse
   * @throws \Google\Service\Exception
   */
  public function answer($servingConfig, GoogleCloudDiscoveryengineV1AnswerQueryRequest $postBody, $optParams = [])
  {
    $params = ['servingConfig' => $servingConfig, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('answer', [$params], GoogleCloudDiscoveryengineV1AnswerQueryResponse::class);
  }
  /**
   * Makes a recommendation, which requires a contextual user event.
   * (servingConfigs.recommend)
   *
   * @param string $servingConfig Required. Full resource name of a ServingConfig:
   * `projects/locations/global/collections/engines/servingConfigs`, or
   * `projects/locations/global/collections/dataStores/servingConfigs` One default
   * serving config is created along with your recommendation engine creation. The
   * engine ID is used as the ID of the default serving config. For example, for
   * Engine `projects/locations/global/collections/engines/my-engine`, you can use
   * `projects/locations/global/collections/engines/my-engine/servingConfigs/my-
   * engine` for your RecommendationService.Recommend requests.
   * @param GoogleCloudDiscoveryengineV1RecommendRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleCloudDiscoveryengineV1RecommendResponse
   * @throws \Google\Service\Exception
   */
  public function recommend($servingConfig, GoogleCloudDiscoveryengineV1RecommendRequest $postBody, $optParams = [])
  {
    $params = ['servingConfig' => $servingConfig, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('recommend', [$params], GoogleCloudDiscoveryengineV1RecommendResponse::class);
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
   * @param GoogleCloudDiscoveryengineV1SearchRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleCloudDiscoveryengineV1SearchResponse
   * @throws \Google\Service\Exception
   */
  public function search($servingConfig, GoogleCloudDiscoveryengineV1SearchRequest $postBody, $optParams = [])
  {
    $params = ['servingConfig' => $servingConfig, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('search', [$params], GoogleCloudDiscoveryengineV1SearchResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsDataStoresServingConfigs::class, 'Google_Service_DiscoveryEngine_Resource_ProjectsLocationsDataStoresServingConfigs');
