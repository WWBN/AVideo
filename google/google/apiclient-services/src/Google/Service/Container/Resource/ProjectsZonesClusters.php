<?php
/*
 * Copyright 2016 Google Inc.
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

/**
 * The "clusters" collection of methods.
 * Typical usage is:
 *  <code>
 *   $containerService = new Google_Service_Container(...);
 *   $clusters = $containerService->clusters;
 *  </code>
 */
class Google_Service_Container_Resource_ProjectsZonesClusters extends Google_Service_Resource
{
  /**
   * Creates a cluster, consisting of the specified number and type of Google
   * Compute Engine instances. By default, the cluster is created in the project's
   * [default network](/compute/docs/networks-and-firewalls#networks). One
   * firewall is added for the cluster. After cluster creation, the cluster
   * creates routes for each node to allow the containers on that node to
   * communicate with all other instances in the cluster. Finally, an entry is
   * added to the project's global metadata indicating which CIDR range is being
   * used by the cluster. (clusters.create)
   *
   * @param string $projectId The Google Developers Console [project ID or project
   * number](https://support.google.com/cloud/answer/6158840).
   * @param string $zone The name of the Google Compute Engine
   * [zone](/compute/docs/zones#available) in which the cluster resides.
   * @param Google_Service_Container_CreateClusterRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Container_Operation
   */
  public function create($projectId, $zone, Google_Service_Container_CreateClusterRequest $postBody, $optParams = array())
  {
    $params = array('projectId' => $projectId, 'zone' => $zone, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Container_Operation");
  }
  /**
   * Deletes the cluster, including the Kubernetes endpoint and all worker nodes.
   * Firewalls and routes that were configured during cluster creation are also
   * deleted. Other Google Compute Engine resources that might be in use by the
   * cluster (e.g. load balancer resources) will not be deleted if they weren't
   * present at the initial create time. (clusters.delete)
   *
   * @param string $projectId The Google Developers Console [project ID or project
   * number](https://support.google.com/cloud/answer/6158840).
   * @param string $zone The name of the Google Compute Engine
   * [zone](/compute/docs/zones#available) in which the cluster resides.
   * @param string $clusterId The name of the cluster to delete.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Container_Operation
   */
  public function delete($projectId, $zone, $clusterId, $optParams = array())
  {
    $params = array('projectId' => $projectId, 'zone' => $zone, 'clusterId' => $clusterId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Container_Operation");
  }
  /**
   * Gets the details of a specific cluster. (clusters.get)
   *
   * @param string $projectId The Google Developers Console [project ID or project
   * number](https://support.google.com/cloud/answer/6158840).
   * @param string $zone The name of the Google Compute Engine
   * [zone](/compute/docs/zones#available) in which the cluster resides.
   * @param string $clusterId The name of the cluster to retrieve.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Container_Cluster
   */
  public function get($projectId, $zone, $clusterId, $optParams = array())
  {
    $params = array('projectId' => $projectId, 'zone' => $zone, 'clusterId' => $clusterId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Container_Cluster");
  }
  /**
   * Lists all clusters owned by a project in either the specified zone or all
   * zones. (clusters.listProjectsZonesClusters)
   *
   * @param string $projectId The Google Developers Console [project ID or project
   * number](https://support.google.com/cloud/answer/6158840).
   * @param string $zone The name of the Google Compute Engine
   * [zone](/compute/docs/zones#available) in which the cluster resides, or "-"
   * for all zones.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Container_ListClustersResponse
   */
  public function listProjectsZonesClusters($projectId, $zone, $optParams = array())
  {
    $params = array('projectId' => $projectId, 'zone' => $zone);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Container_ListClustersResponse");
  }
  /**
   * Updates the settings of a specific cluster. (clusters.update)
   *
   * @param string $projectId The Google Developers Console [project ID or project
   * number](https://support.google.com/cloud/answer/6158840).
   * @param string $zone The name of the Google Compute Engine
   * [zone](/compute/docs/zones#available) in which the cluster resides.
   * @param string $clusterId The name of the cluster to upgrade.
   * @param Google_Service_Container_UpdateClusterRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Container_Operation
   */
  public function update($projectId, $zone, $clusterId, Google_Service_Container_UpdateClusterRequest $postBody, $optParams = array())
  {
    $params = array('projectId' => $projectId, 'zone' => $zone, 'clusterId' => $clusterId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Container_Operation");
  }
}
