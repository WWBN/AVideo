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
 * The "functions" collection of methods.
 * Typical usage is:
 *  <code>
 *   $cloudfunctionsService = new Google_Service_CloudFunctions(...);
 *   $functions = $cloudfunctionsService->functions;
 *  </code>
 */
class Google_Service_CloudFunctions_Resource_ProjectsLocationsFunctions extends Google_Service_Resource
{
  /**
   * Invokes synchronously deployed function. To be used for testing, very limited
   * traffic allowed. (functions.callProjectsLocationsFunctions)
   *
   * @param string $name The name of the function to be called.
   * @param Google_Service_CloudFunctions_CallFunctionRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudFunctions_CallFunctionResponse
   */
  public function callProjectsLocationsFunctions($name, Google_Service_CloudFunctions_CallFunctionRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('call', array($params), "Google_Service_CloudFunctions_CallFunctionResponse");
  }
  /**
   * Creates a new function. If a function with the given name already exists in
   * the specified project, the long running operation will return
   * `ALREADY_EXISTS` error. (functions.create)
   *
   * @param string $location The project and location in which the function should
   * be created, specified in the format `projects/locations`
   * @param Google_Service_CloudFunctions_CloudFunction $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudFunctions_Operation
   */
  public function create($location, Google_Service_CloudFunctions_CloudFunction $postBody, $optParams = array())
  {
    $params = array('location' => $location, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_CloudFunctions_Operation");
  }
  /**
   * Deletes a function with the given name from the specified project. If the
   * given function is used by some trigger, the trigger will be updated to remove
   * this function. (functions.delete)
   *
   * @param string $name The name of the function which should be deleted.
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudFunctions_Operation
   */
  public function delete($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_CloudFunctions_Operation");
  }
  /**
   * Returns a function with the given name from the requested project.
   * (functions.get)
   *
   * @param string $name The name of the function which details should be
   * obtained.
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudFunctions_CloudFunction
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_CloudFunctions_CloudFunction");
  }
  /**
   * Returns a list of functions that belong to the requested project.
   * (functions.listProjectsLocationsFunctions)
   *
   * @param string $location The project and location from which the function
   * should be listed, specified in the format `projects/locations` If you want to
   * list functions in all locations, use "-" in place of a location.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string pageToken The value returned by the last
   * `ListFunctionsResponse`; indicates that this is a continuation of a prior
   * `ListFunctions` call, and that the system should return the next page of
   * data.
   * @opt_param int pageSize Maximum number of functions to return per call.
   * @return Google_Service_CloudFunctions_ListFunctionsResponse
   */
  public function listProjectsLocationsFunctions($location, $optParams = array())
  {
    $params = array('location' => $location);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_CloudFunctions_ListFunctionsResponse");
  }
  /**
   * Updates existing function. (functions.update)
   *
   * @param string $name The name of the function to be updated.
   * @param Google_Service_CloudFunctions_CloudFunction $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudFunctions_Operation
   */
  public function update($name, Google_Service_CloudFunctions_CloudFunction $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_CloudFunctions_Operation");
  }
}
