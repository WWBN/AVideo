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

use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaDisableAdvancedSiteSearchRequest;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaEnableAdvancedSiteSearchRequest;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaRecrawlUrisRequest;
use Google\Service\DiscoveryEngine\GoogleLongrunningOperation;

/**
 * The "siteSearchEngine" collection of methods.
 * Typical usage is:
 *  <code>
 *   $discoveryengineService = new Google\Service\DiscoveryEngine(...);
 *   $siteSearchEngine = $discoveryengineService->projects_locations_dataStores_siteSearchEngine;
 *  </code>
 */
class ProjectsLocationsDataStoresSiteSearchEngine extends \Google\Service\Resource
{
  /**
   * Downgrade from advanced site search to basic site search.
   * (siteSearchEngine.disableAdvancedSiteSearch)
   *
   * @param string $siteSearchEngine Required. Full resource name of the
   * SiteSearchEngine, such as `projects/{project}/locations/{location}/dataStores
   * /{data_store_id}/siteSearchEngine`.
   * @param GoogleCloudDiscoveryengineV1betaDisableAdvancedSiteSearchRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleLongrunningOperation
   * @throws \Google\Service\Exception
   */
  public function disableAdvancedSiteSearch($siteSearchEngine, GoogleCloudDiscoveryengineV1betaDisableAdvancedSiteSearchRequest $postBody, $optParams = [])
  {
    $params = ['siteSearchEngine' => $siteSearchEngine, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('disableAdvancedSiteSearch', [$params], GoogleLongrunningOperation::class);
  }
  /**
   * Upgrade from basic site search to advanced site search.
   * (siteSearchEngine.enableAdvancedSiteSearch)
   *
   * @param string $siteSearchEngine Required. Full resource name of the
   * SiteSearchEngine, such as `projects/{project}/locations/{location}/dataStores
   * /{data_store_id}/siteSearchEngine`.
   * @param GoogleCloudDiscoveryengineV1betaEnableAdvancedSiteSearchRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleLongrunningOperation
   * @throws \Google\Service\Exception
   */
  public function enableAdvancedSiteSearch($siteSearchEngine, GoogleCloudDiscoveryengineV1betaEnableAdvancedSiteSearchRequest $postBody, $optParams = [])
  {
    $params = ['siteSearchEngine' => $siteSearchEngine, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('enableAdvancedSiteSearch', [$params], GoogleLongrunningOperation::class);
  }
  /**
   * Request on-demand recrawl for a list of URIs. (siteSearchEngine.recrawlUris)
   *
   * @param string $siteSearchEngine Required. Full resource name of the
   * SiteSearchEngine, such as
   * `projects/locations/collections/dataStores/siteSearchEngine`.
   * @param GoogleCloudDiscoveryengineV1betaRecrawlUrisRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleLongrunningOperation
   * @throws \Google\Service\Exception
   */
  public function recrawlUris($siteSearchEngine, GoogleCloudDiscoveryengineV1betaRecrawlUrisRequest $postBody, $optParams = [])
  {
    $params = ['siteSearchEngine' => $siteSearchEngine, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('recrawlUris', [$params], GoogleLongrunningOperation::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsDataStoresSiteSearchEngine::class, 'Google_Service_DiscoveryEngine_Resource_ProjectsLocationsDataStoresSiteSearchEngine');
