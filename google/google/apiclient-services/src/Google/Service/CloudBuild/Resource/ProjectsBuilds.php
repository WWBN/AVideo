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
 * The "builds" collection of methods.
 * Typical usage is:
 *  <code>
 *   $cloudbuildService = new Google_Service_CloudBuild(...);
 *   $builds = $cloudbuildService->builds;
 *  </code>
 */
class Google_Service_CloudBuild_Resource_ProjectsBuilds extends Google_Service_Resource
{
  /**
   * Cancels a requested build in progress. (builds.cancel)
   *
   * @param string $projectId ID of the project.
   * @param string $id ID of the build.
   * @param Google_Service_CloudBuild_CancelBuildRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudBuild_Build
   */
  public function cancel($projectId, $id, Google_Service_CloudBuild_CancelBuildRequest $postBody, $optParams = array())
  {
    $params = array('projectId' => $projectId, 'id' => $id, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('cancel', array($params), "Google_Service_CloudBuild_Build");
  }
  /**
   * Starts a build with the specified configuration.
   *
   * The long-running Operation returned by this method will include the ID of the
   * build, which can be passed to GetBuild to determine its status (e.g., success
   * or failure). (builds.create)
   *
   * @param string $projectId ID of the project.
   * @param Google_Service_CloudBuild_Build $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudBuild_Operation
   */
  public function create($projectId, Google_Service_CloudBuild_Build $postBody, $optParams = array())
  {
    $params = array('projectId' => $projectId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_CloudBuild_Operation");
  }
  /**
   * Returns information about a previously requested build.
   *
   * The Build that is returned includes its status (e.g., success or failure, or
   * in-progress), and timing information. (builds.get)
   *
   * @param string $projectId ID of the project.
   * @param string $id ID of the build.
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudBuild_Build
   */
  public function get($projectId, $id, $optParams = array())
  {
    $params = array('projectId' => $projectId, 'id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_CloudBuild_Build");
  }
  /**
   * Lists previously requested builds.
   *
   * Previously requested builds may still be in-progress, or may have finished
   * successfully or unsuccessfully. (builds.listProjectsBuilds)
   *
   * @param string $projectId ID of the project.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter The raw filter text to constrain the results.
   * @opt_param string pageToken Token to provide to skip to a particular spot in
   * the list.
   * @opt_param int pageSize Number of results to return in the list.
   * @return Google_Service_CloudBuild_ListBuildsResponse
   */
  public function listProjectsBuilds($projectId, $optParams = array())
  {
    $params = array('projectId' => $projectId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_CloudBuild_ListBuildsResponse");
  }
}
