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

use Google\Service\DiscoveryEngine\GoogleCloudDiscoveryengineV1ListUserLicensesResponse;

/**
 * The "userLicenses" collection of methods.
 * Typical usage is:
 *  <code>
 *   $discoveryengineService = new Google\Service\DiscoveryEngine(...);
 *   $userLicenses = $discoveryengineService->projects_locations_userStores_userLicenses;
 *  </code>
 */
class ProjectsLocationsUserStoresUserLicenses extends \Google\Service\Resource
{
  /**
   * Lists the User Licenses.
   * (userLicenses.listProjectsLocationsUserStoresUserLicenses)
   *
   * @param string $parent Required. The parent UserStore resource name, format:
   * `projects/{project}/locations/{location}/userStores/{user_store_id}`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter Optional. Filter for the list request. Supported
   * fields: * `license_assignment_state` Examples: * `license_assignment_state =
   * ASSIGNED` to list assigned user licenses. * `license_assignment_state =
   * NO_LICENSE` to list not licensed users. * `license_assignment_state =
   * NO_LICENSE_ATTEMPTED_LOGIN` to list users who attempted login but no license
   * assigned. * `license_assignment_state != NO_LICENSE_ATTEMPTED_LOGIN` to
   * filter out users who attempted login but no license assigned.
   * @opt_param int pageSize Optional. Requested page size. Server may return
   * fewer items than requested. If unspecified, defaults to 10. The maximum value
   * is 50; values above 50 will be coerced to 50. If this field is negative, an
   * INVALID_ARGUMENT error is returned.
   * @opt_param string pageToken Optional. A page token, received from a previous
   * `ListUserLicenses` call. Provide this to retrieve the subsequent page. When
   * paginating, all other parameters provided to `ListUserLicenses` must match
   * the call that provided the page token.
   * @return GoogleCloudDiscoveryengineV1ListUserLicensesResponse
   * @throws \Google\Service\Exception
   */
  public function listProjectsLocationsUserStoresUserLicenses($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], GoogleCloudDiscoveryengineV1ListUserLicensesResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsUserStoresUserLicenses::class, 'Google_Service_DiscoveryEngine_Resource_ProjectsLocationsUserStoresUserLicenses');
