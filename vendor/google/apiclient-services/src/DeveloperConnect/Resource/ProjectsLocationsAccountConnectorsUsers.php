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

namespace Google\Service\DeveloperConnect\Resource;

use Google\Service\DeveloperConnect\FetchAccessTokenRequest;
use Google\Service\DeveloperConnect\FetchAccessTokenResponse;
use Google\Service\DeveloperConnect\ListUsersResponse;
use Google\Service\DeveloperConnect\Operation;
use Google\Service\DeveloperConnect\User;

/**
 * The "users" collection of methods.
 * Typical usage is:
 *  <code>
 *   $developerconnectService = new Google\Service\DeveloperConnect(...);
 *   $users = $developerconnectService->projects_locations_accountConnectors_users;
 *  </code>
 */
class ProjectsLocationsAccountConnectorsUsers extends \Google\Service\Resource
{
  /**
   * Deletes a single User. (users.delete)
   *
   * @param string $name Required. Name of the resource
   * @param array $optParams Optional parameters.
   *
   * @opt_param string etag Optional. This checksum is computed by the server
   * based on the value of other fields, and may be sent on update and delete
   * requests to ensure the client has an up-to-date value before proceeding.
   * @opt_param string requestId Optional. An optional request ID to identify
   * requests. Specify a unique request ID so that if you must retry your request,
   * the server will know to ignore the request if it has already been completed.
   * The server will guarantee that for at least 60 minutes after the first
   * request. For example, consider a situation where you make an initial request
   * and the request times out. If you make the request again with the same
   * request ID, the server can check if original operation with the same request
   * ID was received, and if so, will ignore the second request. This prevents
   * clients from accidentally creating duplicate commitments. The request ID must
   * be a valid UUID with the exception that zero UUID is not supported
   * (00000000-0000-0000-0000-000000000000).
   * @opt_param bool validateOnly Optional. If set, validate the request, but do
   * not actually post it.
   * @return Operation
   * @throws \Google\Service\Exception
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], Operation::class);
  }
  /**
   * Delete the User based on the user credentials. (users.deleteSelf)
   *
   * @param string $name Required. Name of the AccountConnector resource
   * @param array $optParams Optional parameters.
   * @return Operation
   * @throws \Google\Service\Exception
   */
  public function deleteSelf($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('deleteSelf', [$params], Operation::class);
  }
  /**
   * Fetches OAuth access token based on end user credentials.
   * (users.fetchAccessToken)
   *
   * @param string $accountConnector Required. The resource name of the
   * AccountConnector in the format `projects/locations/accountConnectors`.
   * @param FetchAccessTokenRequest $postBody
   * @param array $optParams Optional parameters.
   * @return FetchAccessTokenResponse
   * @throws \Google\Service\Exception
   */
  public function fetchAccessToken($accountConnector, FetchAccessTokenRequest $postBody, $optParams = [])
  {
    $params = ['accountConnector' => $accountConnector, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('fetchAccessToken', [$params], FetchAccessTokenResponse::class);
  }
  /**
   * Fetch the User based on the user credentials. (users.fetchSelf)
   *
   * @param string $name Required. Name of the AccountConnector resource
   * @param array $optParams Optional parameters.
   * @return User
   * @throws \Google\Service\Exception
   */
  public function fetchSelf($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('fetchSelf', [$params], User::class);
  }
  /**
   * Lists Users in a given project, location, and account_connector.
   * (users.listProjectsLocationsAccountConnectorsUsers)
   *
   * @param string $parent Required. Parent value for ListUsersRequest
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter Optional. Filtering results
   * @opt_param string orderBy Optional. Hint for how to order the results
   * @opt_param int pageSize Optional. Requested page size. Server may return
   * fewer items than requested. If unspecified, server will pick an appropriate
   * default.
   * @opt_param string pageToken Optional. A token identifying a page of results
   * the server should return.
   * @return ListUsersResponse
   * @throws \Google\Service\Exception
   */
  public function listProjectsLocationsAccountConnectorsUsers($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListUsersResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsAccountConnectorsUsers::class, 'Google_Service_DeveloperConnect_Resource_ProjectsLocationsAccountConnectorsUsers');
