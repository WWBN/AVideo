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

use Google\Service\DiscoveryEngine\GoogleApiHttpBody;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaImportUserEventsRequest;
use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1betaUserEvent;
use Google\Service\DiscoveryEngine\GoogleLongrunningOperation;

/**
 * The "userEvents" collection of methods.
 * Typical usage is:
 *  <code>
 *   $discoveryengineService = new Google\Service\DiscoveryEngine(...);
 *   $userEvents = $discoveryengineService->projects_locations_collections_dataStores_userEvents;
 *  </code>
 */
class ProjectsLocationsCollectionsDataStoresUserEvents extends \Google\Service\Resource
{
  /**
   * Writes a single user event from the browser. This uses a GET request to due
   * to browser restriction of POST-ing to a 3rd party domain. This method is used
   * only by the Discovery Engine API JavaScript pixel and Google Tag Manager.
   * Users should not call this method directly. (userEvents.collect)
   *
   * @param string $parent Required. The parent DataStore resource name, such as `
   * projects/{project}/locations/{location}/collections/{collection}/dataStores/{
   * data_store}`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string ets The event timestamp in milliseconds. This prevents
   * browser caching of otherwise identical get requests. The name is abbreviated
   * to reduce the payload bytes.
   * @opt_param string uri The URL including cgi-parameters but excluding the hash
   * fragment with a length limit of 5,000 characters. This is often more useful
   * than the referer URL, because many browsers only send the domain for 3rd
   * party requests.
   * @opt_param string userEvent Required. URL encoded UserEvent proto with a
   * length limit of 2,000,000 characters.
   * @return GoogleApiHttpBody
   */
  public function collect($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('collect', [$params], GoogleApiHttpBody::class);
  }
  /**
   * Bulk import of User events. Request processing might be synchronous. Events
   * that already exist are skipped. Use this method for backfilling historical
   * user events. Operation.response is of type ImportResponse. Note that it is
   * possible for a subset of the items to be successfully inserted.
   * Operation.metadata is of type ImportMetadata. (userEvents.import)
   *
   * @param string $parent Required. Parent DataStore resource name, of the form `
   * projects/{project}/locations/{location}/collections/{collection}/dataStores/{
   * data_store}`
   * @param GoogleCloudDiscoveryengineV1betaImportUserEventsRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleLongrunningOperation
   */
  public function import($parent, GoogleCloudDiscoveryengineV1betaImportUserEventsRequest $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('import', [$params], GoogleLongrunningOperation::class);
  }
  /**
   * Writes a single user event. (userEvents.write)
   *
   * @param string $parent Required. The parent DataStore resource name, such as `
   * projects/{project}/locations/{location}/collections/{collection}/dataStores/{
   * data_store}`.
   * @param GoogleCloudDiscoveryengineV1betaUserEvent $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleCloudDiscoveryengineV1betaUserEvent
   */
  public function write($parent, GoogleCloudDiscoveryengineV1betaUserEvent $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('write', [$params], GoogleCloudDiscoveryengineV1betaUserEvent::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsCollectionsDataStoresUserEvents::class, 'Google_Service_DiscoveryEngine_Resource_ProjectsLocationsCollectionsDataStoresUserEvents');
