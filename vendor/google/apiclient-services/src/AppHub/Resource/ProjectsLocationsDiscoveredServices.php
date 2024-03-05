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

namespace Google\Service\AppHub\Resource;

use Google\Service\AppHub\DiscoveredService;
use Google\Service\AppHub\ListDiscoveredServicesResponse;

/**
 * The "discoveredServices" collection of methods.
 * Typical usage is:
 *  <code>
 *   $apphubService = new Google\Service\AppHub(...);
 *   $discoveredServices = $apphubService->projects_locations_discoveredServices;
 *  </code>
 */
class ProjectsLocationsDiscoveredServices extends \Google\Service\Resource
{
  /**
   * Gets a discovered service in a host project and location.
   * (discoveredServices.get)
   *
   * @param string $name Required. Value for name.
   * @param array $optParams Optional parameters.
   * @return DiscoveredService
   * @throws \Google\Service\Exception
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], DiscoveredService::class);
  }
  /**
   * Lists discovered services that can be added to an application in a host
   * project and location.
   * (discoveredServices.listProjectsLocationsDiscoveredServices)
   *
   * @param string $parent Required. Value for parent.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter Optional. Filtering results
   * @opt_param string orderBy Optional. Hint for how to order the results
   * @opt_param int pageSize Optional. Requested page size. Server may return
   * fewer items than requested. If unspecified, server will pick an appropriate
   * default.
   * @opt_param string pageToken Optional. A token identifying a page of results
   * the server should return.
   * @return ListDiscoveredServicesResponse
   * @throws \Google\Service\Exception
   */
  public function listProjectsLocationsDiscoveredServices($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListDiscoveredServicesResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsDiscoveredServices::class, 'Google_Service_AppHub_Resource_ProjectsLocationsDiscoveredServices');
