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

use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1BatchUpdateUserLicensesRequest;
use Google\Service\DiscoveryEngine\GoogleLongrunningOperation;

/**
 * The "userStores" collection of methods.
 * Typical usage is:
 *  <code>
 *   $discoveryengineService = new Google\Service\DiscoveryEngine(...);
 *   $userStores = $discoveryengineService->projects_locations_userStores;
 *  </code>
 */
class ProjectsLocationsUserStores extends \Google\Service\Resource
{
  /**
   * Updates the User License. This method is used for batch assign/unassign
   * licenses to users. (userStores.batchUpdateUserLicenses)
   *
   * @param string $parent Required. The parent UserStore resource name, format:
   * `projects/{project}/locations/{location}/userStores/{user_store_id}`.
   * @param GoogleCloudDiscoveryengineV1BatchUpdateUserLicensesRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleLongrunningOperation
   * @throws \Google\Service\Exception
   */
  public function batchUpdateUserLicenses($parent, GoogleCloudDiscoveryengineV1BatchUpdateUserLicensesRequest $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('batchUpdateUserLicenses', [$params], GoogleLongrunningOperation::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsUserStores::class, 'Google_Service_DiscoveryEngine_Resource_ProjectsLocationsUserStores');
