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
 * The "waiters" collection of methods.
 * Typical usage is:
 *  <code>
 *   $runtimeconfigService = new Google_Service_CloudRuntimeConfig(...);
 *   $waiters = $runtimeconfigService->waiters;
 *  </code>
 */
class Google_Service_CloudRuntimeConfig_Resource_ProjectsConfigsWaiters extends Google_Service_Resource
{
  /**
   * Creates a Waiter resource. This operation returns a long-running Operation
   * resource which can be polled for completion. However, a waiter with the given
   * name will exist (and can be retrieved) prior to the operation completing. If
   * the operation fails, the failed Waiter resource will still exist and must be
   * deleted prior to subsequent creation attempts. (waiters.create)
   *
   * @param string $parent The path to the configuration that will own the waiter.
   * The configuration must exist beforehand; the path must by in the format:
   *
   * `projects/[PROJECT_ID]/configs/[CONFIG_NAME]`.
   * @param Google_Service_CloudRuntimeConfig_Waiter $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string requestId An optional unique request_id. If server receives
   * two Create requests with the same request_id then second request will be
   * ignored and information stored in the backend will be returned. Empty
   * request_id fields are ignored. It is responsibility of the client to ensure
   * uniqueness of the request_id strings. The strings are limited to 64
   * characters.
   * @return Google_Service_CloudRuntimeConfig_Operation
   */
  public function create($parent, Google_Service_CloudRuntimeConfig_Waiter $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_CloudRuntimeConfig_Operation");
  }
  /**
   * Deletes the waiter with the specified name. (waiters.delete)
   *
   * @param string $name The Waiter resource to delete, in the format:
   *
   *  `projects/[PROJECT_ID]/configs/[CONFIG_NAME]/waiters/[WAITER_NAME]`
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudRuntimeConfig_RuntimeconfigEmpty
   */
  public function delete($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_CloudRuntimeConfig_RuntimeconfigEmpty");
  }
  /**
   * Gets information about a single waiter. (waiters.get)
   *
   * @param string $name The fully-qualified name of the Waiter resource object to
   * retrieve, in the format:
   *
   * `projects/[PROJECT_ID]/configs/[CONFIG_NAME]/waiters/[WAITER_NAME]`
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudRuntimeConfig_Waiter
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_CloudRuntimeConfig_Waiter");
  }
  /**
   * List waiters within the given configuration.
   * (waiters.listProjectsConfigsWaiters)
   *
   * @param string $parent The path to the configuration for which you want to get
   * a list of waiters. The configuration must exist beforehand; the path must by
   * in the format:
   *
   * `projects/[PROJECT_ID]/configs/[CONFIG_NAME]`
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Specifies the number of results to return per page.
   * If there are fewer elements than the specified number, returns all elements.
   * @opt_param string pageToken Specifies a page token to use. Set `pageToken` to
   * a `nextPageToken` returned by a previous list request to get the next page of
   * results.
   * @return Google_Service_CloudRuntimeConfig_ListWaitersResponse
   */
  public function listProjectsConfigsWaiters($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_CloudRuntimeConfig_ListWaitersResponse");
  }
}
