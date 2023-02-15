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

namespace Google\Service\Dataform\Resource;

use Google\Service\Dataform\DataformEmpty;
use Google\Service\Dataform\FetchRemoteBranchesResponse;
use Google\Service\Dataform\ListRepositoriesResponse;
use Google\Service\Dataform\Repository;

/**
 * The "repositories" collection of methods.
 * Typical usage is:
 *  <code>
 *   $dataformService = new Google\Service\Dataform(...);
 *   $repositories = $dataformService->projects_locations_repositories;
 *  </code>
 */
class ProjectsLocationsRepositories extends \Google\Service\Resource
{
  /**
   * Creates a new Repository in a given project and location.
   * (repositories.create)
   *
   * @param string $parent Required. The location in which to create the
   * repository. Must be in the format `projects/locations`.
   * @param Repository $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string repositoryId Required. The ID to use for the repository,
   * which will become the final component of the repository's resource name.
   * @return Repository
   */
  public function create($parent, Repository $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], Repository::class);
  }
  /**
   * Deletes a single Repository. (repositories.delete)
   *
   * @param string $name Required. The repository's name.
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool force If set to true, any child resources of this repository
   * will also be deleted. (Otherwise, the request will only succeed if the
   * repository has no child resources.)
   * @return DataformEmpty
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], DataformEmpty::class);
  }
  /**
   * Fetches a Repository's remote branches. (repositories.fetchRemoteBranches)
   *
   * @param string $name Required. The repository's name.
   * @param array $optParams Optional parameters.
   * @return FetchRemoteBranchesResponse
   */
  public function fetchRemoteBranches($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('fetchRemoteBranches', [$params], FetchRemoteBranchesResponse::class);
  }
  /**
   * Fetches a single Repository. (repositories.get)
   *
   * @param string $name Required. The repository's name.
   * @param array $optParams Optional parameters.
   * @return Repository
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], Repository::class);
  }
  /**
   * Lists Repositories in a given project and location.
   * (repositories.listProjectsLocationsRepositories)
   *
   * @param string $parent Required. The location in which to list repositories.
   * Must be in the format `projects/locations`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter Optional. Filter for the returned list.
   * @opt_param string orderBy Optional. This field only supports ordering by
   * `name`. If unspecified, the server will choose the ordering. If specified,
   * the default order is ascending for the `name` field.
   * @opt_param int pageSize Optional. Maximum number of repositories to return.
   * The server may return fewer items than requested. If unspecified, the server
   * will pick an appropriate default.
   * @opt_param string pageToken Optional. Page token received from a previous
   * `ListRepositories` call. Provide this to retrieve the subsequent page. When
   * paginating, all other parameters provided to `ListRepositories` must match
   * the call that provided the page token.
   * @return ListRepositoriesResponse
   */
  public function listProjectsLocationsRepositories($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListRepositoriesResponse::class);
  }
  /**
   * Updates a single Repository. (repositories.patch)
   *
   * @param string $name Output only. The repository's name.
   * @param Repository $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask Optional. Specifies the fields to be updated in
   * the repository. If left unset, all fields will be updated.
   * @return Repository
   */
  public function patch($name, Repository $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], Repository::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsRepositories::class, 'Google_Service_Dataform_Resource_ProjectsLocationsRepositories');
