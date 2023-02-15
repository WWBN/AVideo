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

namespace Google\Service\DatabaseMigrationService\Resource;

use Google\Service\DatabaseMigrationService\ListPrivateConnectionsResponse;
use Google\Service\DatabaseMigrationService\Operation;
use Google\Service\DatabaseMigrationService\PrivateConnection;

/**
 * The "privateConnections" collection of methods.
 * Typical usage is:
 *  <code>
 *   $datamigrationService = new Google\Service\DatabaseMigrationService(...);
 *   $privateConnections = $datamigrationService->projects_locations_privateConnections;
 *  </code>
 */
class ProjectsLocationsPrivateConnections extends \Google\Service\Resource
{
  /**
   * Creates a new private connection in a given project and location.
   * (privateConnections.create)
   *
   * @param string $parent Required. The parent that owns the collection of
   * PrivateConnections.
   * @param PrivateConnection $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string privateConnectionId Required. The private connection
   * identifier.
   * @opt_param string requestId Optional. A unique id used to identify the
   * request. If the server receives two requests with the same id, then the
   * second request will be ignored. It is recommended to always set this value to
   * a UUID. The id must contain only letters (a-z, A-Z), numbers (0-9),
   * underscores (_), and hyphens (-). The maximum length is 40 characters.
   * @opt_param bool skipValidation Optional. If set to true, will skip
   * validations.
   * @return Operation
   */
  public function create($parent, PrivateConnection $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], Operation::class);
  }
  /**
   * Deletes a single Database Migration Service private connection.
   * (privateConnections.delete)
   *
   * @param string $name Required. The name of the private connection to delete.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string requestId Optional. A unique id used to identify the
   * request. If the server receives two requests with the same id, then the
   * second request will be ignored. It is recommended to always set this value to
   * a UUID. The id must contain only letters (a-z, A-Z), numbers (0-9),
   * underscores (_), and hyphens (-). The maximum length is 40 characters.
   * @return Operation
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], Operation::class);
  }
  /**
   * Gets details of a single private connection. (privateConnections.get)
   *
   * @param string $name Required. The name of the private connection to get.
   * @param array $optParams Optional parameters.
   * @return PrivateConnection
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], PrivateConnection::class);
  }
  /**
   * Retrieves a list of private connections in a given project and location.
   * (privateConnections.listProjectsLocationsPrivateConnections)
   *
   * @param string $parent Required. The parent that owns the collection of
   * private connections.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter A filter expression that filters private connections
   * listed in the response. The expression must specify the field name, a
   * comparison operator, and the value that you want to use for filtering. The
   * value must be a string, a number, or a boolean. The comparison operator must
   * be either =, !=, >, or <. For example, list private connections created this
   * year by specifying **createTime %gt; 2021-01-01T00:00:00.000000000Z**.
   * @opt_param string orderBy Order by fields for the result.
   * @opt_param int pageSize Maximum number of private connections to return. If
   * unspecified, at most 50 private connections that will be returned. The
   * maximum value is 1000; values above 1000 will be coerced to 1000.
   * @opt_param string pageToken Page token received from a previous
   * `ListPrivateConnections` call. Provide this to retrieve the subsequent page.
   * When paginating, all other parameters provided to `ListPrivateConnections`
   * must match the call that provided the page token.
   * @return ListPrivateConnectionsResponse
   */
  public function listProjectsLocationsPrivateConnections($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListPrivateConnectionsResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsPrivateConnections::class, 'Google_Service_DatabaseMigrationService_Resource_ProjectsLocationsPrivateConnections');
