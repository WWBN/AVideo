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
 * The "variables" collection of methods.
 * Typical usage is:
 *  <code>
 *   $runtimeconfigService = new Google_Service_CloudRuntimeConfig(...);
 *   $variables = $runtimeconfigService->variables;
 *  </code>
 */
class Google_Service_CloudRuntimeConfig_Resource_ProjectsConfigsVariables extends Google_Service_Resource
{
  /**
   * Creates a variable within the given configuration. You cannot create a
   * variable with a name that is a prefix of an existing variable name, or a name
   * that has an existing variable name as a prefix.
   *
   * To learn more about creating a variable, read the [Setting and Getting Data
   * ](/deployment-manager/runtime-configurator/set-and-get-variables)
   * documentation. (variables.create)
   *
   * @param string $parent The path to the RutimeConfig resource that this
   * variable should belong to. The configuration must exist beforehand; the path
   * must by in the format:
   *
   * `projects/[PROJECT_ID]/configs/[CONFIG_NAME]`
   * @param Google_Service_CloudRuntimeConfig_Variable $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string requestId An optional unique request_id. If server receives
   * two Create requests with the same request_id then second request will be
   * ignored and the resource stored in the backend will be returned. Empty
   * request_id fields are ignored. It is responsibility of the client to ensure
   * uniqueness of the request_id strings. The strings are limited to 64
   * characters.
   * @return Google_Service_CloudRuntimeConfig_Variable
   */
  public function create($parent, Google_Service_CloudRuntimeConfig_Variable $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_CloudRuntimeConfig_Variable");
  }
  /**
   * Deletes a variable or multiple variables.
   *
   * If you specify a variable name, then that variable is deleted. If you specify
   * a prefix and `recursive` is true, then all variables with that prefix are
   * deleted. You must set a `recursive` to true if you delete variables by
   * prefix. (variables.delete)
   *
   * @param string $name The name of the variable to delete, in the format:
   *
   * `projects/[PROJECT_ID]/configs/[CONFIG_NAME]/variables/[VARIABLE_NAME]`
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool recursive Set to `true` to recursively delete multiple
   * variables with the same prefix.
   * @return Google_Service_CloudRuntimeConfig_RuntimeconfigEmpty
   */
  public function delete($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_CloudRuntimeConfig_RuntimeconfigEmpty");
  }
  /**
   * Gets information about a single variable. (variables.get)
   *
   * @param string $name The name of the variable to return, in the format:
   *
   * `projects/[PROJECT_ID]/configs/[CONFIG_NAME]/variables/[VARIBLE_NAME]`
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudRuntimeConfig_Variable
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_CloudRuntimeConfig_Variable");
  }
  /**
   * Lists variables within given a configuration, matching any provided filters.
   * This only lists variable names, not the values.
   * (variables.listProjectsConfigsVariables)
   *
   * @param string $parent The path to the RuntimeConfig resource for which you
   * want to list variables. The configuration must exist beforehand; the path
   * must by in the format:
   *
   * `projects/[PROJECT_ID]/configs/[CONFIG_NAME]`
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Specifies the number of results to return per page.
   * If there are fewer elements than the specified number, returns all elements.
   * @opt_param string filter Filters variables by matching the specified filter.
   * For example:
   *
   * `projects/example-project/config/[CONFIG_NAME]/variables/example-variable`.
   * @opt_param string pageToken Specifies a page token to use. Set `pageToken` to
   * a `nextPageToken` returned by a previous list request to get the next page of
   * results.
   * @return Google_Service_CloudRuntimeConfig_ListVariablesResponse
   */
  public function listProjectsConfigsVariables($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_CloudRuntimeConfig_ListVariablesResponse");
  }
  /**
   * Updates an existing variable with a new value. (variables.update)
   *
   * @param string $name The name of the variable to update, in the format:
   *
   * `projects/[PROJECT_ID]/configs/[CONFIG_NAME]/variables/[VARIABLE_NAME]`
   * @param Google_Service_CloudRuntimeConfig_Variable $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudRuntimeConfig_Variable
   */
  public function update($name, Google_Service_CloudRuntimeConfig_Variable $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_CloudRuntimeConfig_Variable");
  }
  /**
   * Watches a specific variable and waits for a change in the variable's value.
   * When there is a change, this method returns the new value or times out.
   *
   * If a variable is deleted while being watched, the `variableState` state is
   * set to `DELETED` and the method returns the last known variable `value`.
   *
   * If you set the deadline for watching to a larger value than internal timeout
   * (60 seconds), the current variable value is returned and the `variableState`
   * will be `VARIABLE_STATE_UNSPECIFIED`.
   *
   * To learn more about creating a watcher, read the [Watching a Variable for
   * Changes](/deployment-manager/runtime-configurator/watching-a-variable)
   * documentation. (variables.watch)
   *
   * @param string $name The name of the variable to watch, in the format:
   *
   * `projects/[PROJECT_ID]/configs/[CONFIG_NAME]`
   * @param Google_Service_CloudRuntimeConfig_WatchVariableRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudRuntimeConfig_Variable
   */
  public function watch($name, Google_Service_CloudRuntimeConfig_WatchVariableRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('watch', array($params), "Google_Service_CloudRuntimeConfig_Variable");
  }
}
