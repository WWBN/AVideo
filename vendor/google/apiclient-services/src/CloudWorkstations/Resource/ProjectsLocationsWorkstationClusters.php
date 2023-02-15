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

namespace Google\Service\CloudWorkstations\Resource;

use Google\Service\CloudWorkstations\ListWorkstationClustersResponse;
use Google\Service\CloudWorkstations\Operation;
use Google\Service\CloudWorkstations\WorkstationCluster;

/**
 * The "workstationClusters" collection of methods.
 * Typical usage is:
 *  <code>
 *   $workstationsService = new Google\Service\CloudWorkstations(...);
 *   $workstationClusters = $workstationsService->projects_locations_workstationClusters;
 *  </code>
 */
class ProjectsLocationsWorkstationClusters extends \Google\Service\Resource
{
  /**
   * Creates a new workstation cluster. (workstationClusters.create)
   *
   * @param string $parent Required. Parent resource name.
   * @param WorkstationCluster $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool validateOnly If set, validate the request and preview the
   * review, but do not actually apply it.
   * @opt_param string workstationClusterId Required. ID to use for the cluster.
   * @return Operation
   */
  public function create($parent, WorkstationCluster $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], Operation::class);
  }
  /**
   * Deletes the specified workstation cluster. (workstationClusters.delete)
   *
   * @param string $name Required. Name of the cluster to delete.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string etag If set, the request will be rejected if the latest
   * version of the cluster on the server does not have this etag.
   * @opt_param bool force If set, any workstation configurations and workstations
   * in the cluster will also be deleted. Otherwise, the request will work only if
   * the cluster has no configurations or workstations.
   * @opt_param bool validateOnly If set, validate the request and preview the
   * review, but do not actually apply it.
   * @return Operation
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], Operation::class);
  }
  /**
   * Returns the requested workstation cluster. (workstationClusters.get)
   *
   * @param string $name Required. Name of the requested resource.
   * @param array $optParams Optional parameters.
   * @return WorkstationCluster
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], WorkstationCluster::class);
  }
  /**
   * Returns all workstation clusters in the specified location.
   * (workstationClusters.listProjectsLocationsWorkstationClusters)
   *
   * @param string $parent Required. Parent resource name.
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Maximum number of items to return.
   * @opt_param string pageToken next_page_token value returned from a previous
   * List request, if any.
   * @return ListWorkstationClustersResponse
   */
  public function listProjectsLocationsWorkstationClusters($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListWorkstationClustersResponse::class);
  }
  /**
   * Updates an existing workstation cluster. (workstationClusters.patch)
   *
   * @param string $name Full name of this resource.
   * @param WorkstationCluster $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool allowMissing If set, and the cluster is not found, a new
   * cluster will be created. In this situation, update_mask is ignored.
   * @opt_param string updateMask Required. Mask specifying which fields in the
   * cluster should be updated.
   * @opt_param bool validateOnly If set, validate the request and preview the
   * review, but do not actually apply it.
   * @return Operation
   */
  public function patch($name, WorkstationCluster $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], Operation::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsWorkstationClusters::class, 'Google_Service_CloudWorkstations_Resource_ProjectsLocationsWorkstationClusters');
