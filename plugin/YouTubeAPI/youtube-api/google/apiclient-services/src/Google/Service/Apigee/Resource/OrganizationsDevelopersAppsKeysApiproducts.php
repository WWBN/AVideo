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

/**
 * The "apiproducts" collection of methods.
 * Typical usage is:
 *  <code>
 *   $apigeeService = new Google_Service_Apigee(...);
 *   $apiproducts = $apigeeService->apiproducts;
 *  </code>
 */
class Google_Service_Apigee_Resource_OrganizationsDevelopersAppsKeysApiproducts extends Google_Service_Resource
{
  /**
   * Removes an API product from an app's consumer key, and thereby renders the
   * app unable to access the API resources defined in that API product. Note :
   * The consumer key itself still exists after this call. Only the association of
   * the key with the API product is removed. (apiproducts.delete)
   *
   * @param string $name Resource name of a api product in a developer app key `or
   * ganizations/{org}/developers/{developer}/apps/{app}/keys/{key}/apiproducts/{a
   * piproduct}`
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey
   */
  public function delete($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey");
  }
  /**
   * Approve or Revoke the key for a given api product.
   * (apiproducts.updateDeveloperAppKeyApiProduct)
   *
   * @param string $name Resource name of a api product in a developer app key `or
   * ganizations/{org}/developers/{developer}/apps/{app}/keys/{key}/apiproducts/{a
   * piproduct}`
   * @param array $optParams Optional parameters.
   *
   * @opt_param string action Set the action to approve or revoke.
   * @return Google_Service_Apigee_GoogleProtobufEmpty
   */
  public function updateDeveloperAppKeyApiProduct($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('updateDeveloperAppKeyApiProduct', array($params), "Google_Service_Apigee_GoogleProtobufEmpty");
  }
}
