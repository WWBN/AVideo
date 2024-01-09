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

namespace Google\Service\GKEHub\Resource;

use Google\Service\GKEHub\ListScopesResponse;
use Google\Service\GKEHub\Operation;
use Google\Service\GKEHub\Scope;

/**
 * The "scopes" collection of methods.
 * Typical usage is:
 *  <code>
 *   $gkehubService = new Google\Service\GKEHub(...);
 *   $scopes = $gkehubService->projects_locations_scopes;
 *  </code>
 */
class ProjectsLocationsScopes extends \Google\Service\Resource
{
  /**
   * Creates a Scope. (scopes.create)
   *
   * @param string $parent Required. The parent (project and location) where the
   * Scope will be created. Specified in the format `projects/locations`.
   * @param Scope $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string scopeId Required. Client chosen ID for the Scope.
   * `scope_id` must be a ????
   * @return Operation
   */
  public function create($parent, Scope $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], Operation::class);
  }
  /**
   * Deletes a Scope. (scopes.delete)
   *
   * @param string $name Required. The Scope resource name in the format
   * `projects/locations/scopes`.
   * @param array $optParams Optional parameters.
   * @return Operation
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], Operation::class);
  }
  /**
   * Returns the details of a Scope. (scopes.get)
   *
   * @param string $name Required. The Scope resource name in the format
   * `projects/locations/scopes`.
   * @param array $optParams Optional parameters.
   * @return Scope
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], Scope::class);
  }
  /**
   * Lists Scopes. (scopes.listProjectsLocationsScopes)
   *
   * @param string $parent Required. The parent (project and location) where the
   * Scope will be listed. Specified in the format `projects/locations`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Optional. When requesting a 'page' of resources,
   * `page_size` specifies number of resources to return. If unspecified or set to
   * 0, all resources will be returned.
   * @opt_param string pageToken Optional. Token returned by previous call to
   * `ListScopes` which specifies the position in the list from where to continue
   * listing the resources.
   * @return ListScopesResponse
   */
  public function listProjectsLocationsScopes($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListScopesResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsScopes::class, 'Google_Service_GKEHub_Resource_ProjectsLocationsScopes');
