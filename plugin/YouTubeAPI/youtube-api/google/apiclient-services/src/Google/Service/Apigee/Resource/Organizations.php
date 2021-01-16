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
 * The "organizations" collection of methods.
 * Typical usage is:
 *  <code>
 *   $apigeeService = new Google_Service_Apigee(...);
 *   $organizations = $apigeeService->organizations;
 *  </code>
 */
class Google_Service_Apigee_Resource_Organizations extends Google_Service_Resource
{
  /**
   * Creates an Organization. Only Name and Analytics Region will be used from the
   * request body. (organizations.create)
   *
   * @param Google_Service_Apigee_GoogleCloudApigeeV1Organization $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string parent Required. The name of the project in which to
   * associate the organization. Values are of the form `projects/`.
   * @return Google_Service_Apigee_GoogleLongrunningOperation
   */
  public function create(Google_Service_Apigee_GoogleCloudApigeeV1Organization $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Apigee_GoogleLongrunningOperation");
  }
  /**
   * Gets an Organization. (organizations.get)
   *
   * @param string $name Required. Organization resource name of the form:
   * `organizations/{organization_id}`
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Organization
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Organization");
  }
  /**
   * Gets an Organization's. (organizations.getSyncAuthorization)
   *
   * @param string $name Required. Organization resource name of the form:
   * `organizations/{organization_id}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1GetSyncAuthorizationRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1SyncAuthorization
   */
  public function getSyncAuthorization($name, Google_Service_Apigee_GoogleCloudApigeeV1GetSyncAuthorizationRequest $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('getSyncAuthorization', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1SyncAuthorization");
  }
  /**
   * Lists the Apigee organizations, and the related projects that a user has
   * permissions for. This call will be used by the Unified Experience in order to
   * populate the list of Apigee organizations in a dropdown that the user has
   * access to. (organizations.listOrganizations)
   *
   * @param string $parent Required. Must be of the form `organizations`.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1ListOrganizationsResponse
   */
  public function listOrganizations($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1ListOrganizationsResponse");
  }
  /**
   * Updates an Organization's. (organizations.setSyncAuthorization)
   *
   * @param string $name Required. Organization resource name of the form:
   * `organizations/{organization_id}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1SyncAuthorization $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1SyncAuthorization
   */
  public function setSyncAuthorization($name, Google_Service_Apigee_GoogleCloudApigeeV1SyncAuthorization $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setSyncAuthorization', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1SyncAuthorization");
  }
  /**
   * Updates an Organization's properties. No other fields will be updated.
   * (organizations.update)
   *
   * @param string $name Required. Organization resource name of the form:
   * `organizations/{organization_id}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1Organization $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Organization
   */
  public function update($name, Google_Service_Apigee_GoogleCloudApigeeV1Organization $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Organization");
  }
  /**
   * Updates an Organization's properties. No other fields will be updated.
   * (organizations.updateOrganization)
   *
   * @param string $name Required. Organization resource name of the form:
   * `organizations/{organization_id}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1Organization $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Organization
   */
  public function updateOrganization($name, Google_Service_Apigee_GoogleCloudApigeeV1Organization $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('updateOrganization', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Organization");
  }
}
