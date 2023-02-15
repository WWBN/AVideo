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

use Google\Service\Dataform\CommitWorkspaceChangesRequest;
use Google\Service\Dataform\DataformEmpty;
use Google\Service\Dataform\FetchFileDiffResponse;
use Google\Service\Dataform\FetchFileGitStatusesResponse;
use Google\Service\Dataform\FetchGitAheadBehindResponse;
use Google\Service\Dataform\InstallNpmPackagesRequest;
use Google\Service\Dataform\InstallNpmPackagesResponse;
use Google\Service\Dataform\ListWorkspacesResponse;
use Google\Service\Dataform\MakeDirectoryRequest;
use Google\Service\Dataform\MakeDirectoryResponse;
use Google\Service\Dataform\MoveDirectoryRequest;
use Google\Service\Dataform\MoveDirectoryResponse;
use Google\Service\Dataform\MoveFileRequest;
use Google\Service\Dataform\MoveFileResponse;
use Google\Service\Dataform\PullGitCommitsRequest;
use Google\Service\Dataform\PushGitCommitsRequest;
use Google\Service\Dataform\QueryDirectoryContentsResponse;
use Google\Service\Dataform\ReadFileResponse;
use Google\Service\Dataform\RemoveDirectoryRequest;
use Google\Service\Dataform\RemoveFileRequest;
use Google\Service\Dataform\ResetWorkspaceChangesRequest;
use Google\Service\Dataform\Workspace;
use Google\Service\Dataform\WriteFileRequest;
use Google\Service\Dataform\WriteFileResponse;

/**
 * The "workspaces" collection of methods.
 * Typical usage is:
 *  <code>
 *   $dataformService = new Google\Service\Dataform(...);
 *   $workspaces = $dataformService->projects_locations_repositories_workspaces;
 *  </code>
 */
class ProjectsLocationsRepositoriesWorkspaces extends \Google\Service\Resource
{
  /**
   * Applies a Git commit for uncommitted files in a Workspace.
   * (workspaces.commit)
   *
   * @param string $name Required. The workspace's name.
   * @param CommitWorkspaceChangesRequest $postBody
   * @param array $optParams Optional parameters.
   * @return DataformEmpty
   */
  public function commit($name, CommitWorkspaceChangesRequest $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('commit', [$params], DataformEmpty::class);
  }
  /**
   * Creates a new Workspace in a given Repository. (workspaces.create)
   *
   * @param string $parent Required. The repository in which to create the
   * workspace. Must be in the format `projects/locations/repositories`.
   * @param Workspace $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string workspaceId Required. The ID to use for the workspace,
   * which will become the final component of the workspace's resource name.
   * @return Workspace
   */
  public function create($parent, Workspace $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], Workspace::class);
  }
  /**
   * Deletes a single Workspace. (workspaces.delete)
   *
   * @param string $name Required. The workspace resource's name.
   * @param array $optParams Optional parameters.
   * @return DataformEmpty
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], DataformEmpty::class);
  }
  /**
   * Fetches Git diff for an uncommitted file in a Workspace.
   * (workspaces.fetchFileDiff)
   *
   * @param string $workspace Required. The workspace's name.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string path Required. The file's full path including filename,
   * relative to the workspace root.
   * @return FetchFileDiffResponse
   */
  public function fetchFileDiff($workspace, $optParams = [])
  {
    $params = ['workspace' => $workspace];
    $params = array_merge($params, $optParams);
    return $this->call('fetchFileDiff', [$params], FetchFileDiffResponse::class);
  }
  /**
   * Fetches Git statuses for the files in a Workspace.
   * (workspaces.fetchFileGitStatuses)
   *
   * @param string $name Required. The workspace's name.
   * @param array $optParams Optional parameters.
   * @return FetchFileGitStatusesResponse
   */
  public function fetchFileGitStatuses($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('fetchFileGitStatuses', [$params], FetchFileGitStatusesResponse::class);
  }
  /**
   * Fetches Git ahead/behind against a remote branch.
   * (workspaces.fetchGitAheadBehind)
   *
   * @param string $name Required. The workspace's name.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string remoteBranch Optional. The name of the branch in the Git
   * remote against which this workspace should be compared. If left unset, the
   * repository's default branch name will be used.
   * @return FetchGitAheadBehindResponse
   */
  public function fetchGitAheadBehind($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('fetchGitAheadBehind', [$params], FetchGitAheadBehindResponse::class);
  }
  /**
   * Fetches a single Workspace. (workspaces.get)
   *
   * @param string $name Required. The workspace's name.
   * @param array $optParams Optional parameters.
   * @return Workspace
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], Workspace::class);
  }
  /**
   * Installs dependency NPM packages (inside a Workspace).
   * (workspaces.installNpmPackages)
   *
   * @param string $workspace Required. The workspace's name.
   * @param InstallNpmPackagesRequest $postBody
   * @param array $optParams Optional parameters.
   * @return InstallNpmPackagesResponse
   */
  public function installNpmPackages($workspace, InstallNpmPackagesRequest $postBody, $optParams = [])
  {
    $params = ['workspace' => $workspace, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('installNpmPackages', [$params], InstallNpmPackagesResponse::class);
  }
  /**
   * Lists Workspaces in a given Repository.
   * (workspaces.listProjectsLocationsRepositoriesWorkspaces)
   *
   * @param string $parent Required. The repository in which to list workspaces.
   * Must be in the format `projects/locations/repositories`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter Optional. Filter for the returned list.
   * @opt_param string orderBy Optional. This field only supports ordering by
   * `name`. If unspecified, the server will choose the ordering. If specified,
   * the default order is ascending for the `name` field.
   * @opt_param int pageSize Optional. Maximum number of workspaces to return. The
   * server may return fewer items than requested. If unspecified, the server will
   * pick an appropriate default.
   * @opt_param string pageToken Optional. Page token received from a previous
   * `ListWorkspaces` call. Provide this to retrieve the subsequent page. When
   * paginating, all other parameters provided to `ListWorkspaces` must match the
   * call that provided the page token.
   * @return ListWorkspacesResponse
   */
  public function listProjectsLocationsRepositoriesWorkspaces($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListWorkspacesResponse::class);
  }
  /**
   * Creates a directory inside a Workspace. (workspaces.makeDirectory)
   *
   * @param string $workspace Required. The workspace's name.
   * @param MakeDirectoryRequest $postBody
   * @param array $optParams Optional parameters.
   * @return MakeDirectoryResponse
   */
  public function makeDirectory($workspace, MakeDirectoryRequest $postBody, $optParams = [])
  {
    $params = ['workspace' => $workspace, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('makeDirectory', [$params], MakeDirectoryResponse::class);
  }
  /**
   * Moves a directory (inside a Workspace), and all of its contents, to a new
   * location. (workspaces.moveDirectory)
   *
   * @param string $workspace Required. The workspace's name.
   * @param MoveDirectoryRequest $postBody
   * @param array $optParams Optional parameters.
   * @return MoveDirectoryResponse
   */
  public function moveDirectory($workspace, MoveDirectoryRequest $postBody, $optParams = [])
  {
    $params = ['workspace' => $workspace, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('moveDirectory', [$params], MoveDirectoryResponse::class);
  }
  /**
   * Moves a file (inside a Workspace) to a new location. (workspaces.moveFile)
   *
   * @param string $workspace Required. The workspace's name.
   * @param MoveFileRequest $postBody
   * @param array $optParams Optional parameters.
   * @return MoveFileResponse
   */
  public function moveFile($workspace, MoveFileRequest $postBody, $optParams = [])
  {
    $params = ['workspace' => $workspace, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('moveFile', [$params], MoveFileResponse::class);
  }
  /**
   * Pulls Git commits from the Repository's remote into a Workspace.
   * (workspaces.pull)
   *
   * @param string $name Required. The workspace's name.
   * @param PullGitCommitsRequest $postBody
   * @param array $optParams Optional parameters.
   * @return DataformEmpty
   */
  public function pull($name, PullGitCommitsRequest $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('pull', [$params], DataformEmpty::class);
  }
  /**
   * Pushes Git commits from a Workspace to the Repository's remote.
   * (workspaces.push)
   *
   * @param string $name Required. The workspace's name.
   * @param PushGitCommitsRequest $postBody
   * @param array $optParams Optional parameters.
   * @return DataformEmpty
   */
  public function push($name, PushGitCommitsRequest $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('push', [$params], DataformEmpty::class);
  }
  /**
   * Returns the contents of a given Workspace directory.
   * (workspaces.queryDirectoryContents)
   *
   * @param string $workspace Required. The workspace's name.
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Optional. Maximum number of paths to return. The
   * server may return fewer items than requested. If unspecified, the server will
   * pick an appropriate default.
   * @opt_param string pageToken Optional. Page token received from a previous
   * `QueryDirectoryContents` call. Provide this to retrieve the subsequent page.
   * When paginating, all other parameters provided to `QueryDirectoryContents`
   * must match the call that provided the page token.
   * @opt_param string path Optional. The directory's full path including
   * directory name, relative to the workspace root. If left unset, the workspace
   * root is used.
   * @return QueryDirectoryContentsResponse
   */
  public function queryDirectoryContents($workspace, $optParams = [])
  {
    $params = ['workspace' => $workspace];
    $params = array_merge($params, $optParams);
    return $this->call('queryDirectoryContents', [$params], QueryDirectoryContentsResponse::class);
  }
  /**
   * Returns the contents of a file (inside a Workspace). (workspaces.readFile)
   *
   * @param string $workspace Required. The workspace's name.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string path Required. The file's full path including filename,
   * relative to the workspace root.
   * @return ReadFileResponse
   */
  public function readFile($workspace, $optParams = [])
  {
    $params = ['workspace' => $workspace];
    $params = array_merge($params, $optParams);
    return $this->call('readFile', [$params], ReadFileResponse::class);
  }
  /**
   * Deletes a directory (inside a Workspace) and all of its contents.
   * (workspaces.removeDirectory)
   *
   * @param string $workspace Required. The workspace's name.
   * @param RemoveDirectoryRequest $postBody
   * @param array $optParams Optional parameters.
   * @return DataformEmpty
   */
  public function removeDirectory($workspace, RemoveDirectoryRequest $postBody, $optParams = [])
  {
    $params = ['workspace' => $workspace, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('removeDirectory', [$params], DataformEmpty::class);
  }
  /**
   * Deletes a file (inside a Workspace). (workspaces.removeFile)
   *
   * @param string $workspace Required. The workspace's name.
   * @param RemoveFileRequest $postBody
   * @param array $optParams Optional parameters.
   * @return DataformEmpty
   */
  public function removeFile($workspace, RemoveFileRequest $postBody, $optParams = [])
  {
    $params = ['workspace' => $workspace, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('removeFile', [$params], DataformEmpty::class);
  }
  /**
   * Performs a Git reset for uncommitted files in a Workspace. (workspaces.reset)
   *
   * @param string $name Required. The workspace's name.
   * @param ResetWorkspaceChangesRequest $postBody
   * @param array $optParams Optional parameters.
   * @return DataformEmpty
   */
  public function reset($name, ResetWorkspaceChangesRequest $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('reset', [$params], DataformEmpty::class);
  }
  /**
   * Writes to a file (inside a Workspace). (workspaces.writeFile)
   *
   * @param string $workspace Required. The workspace's name.
   * @param WriteFileRequest $postBody
   * @param array $optParams Optional parameters.
   * @return WriteFileResponse
   */
  public function writeFile($workspace, WriteFileRequest $postBody, $optParams = [])
  {
    $params = ['workspace' => $workspace, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('writeFile', [$params], WriteFileResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsRepositoriesWorkspaces::class, 'Google_Service_Dataform_Resource_ProjectsLocationsRepositoriesWorkspaces');
