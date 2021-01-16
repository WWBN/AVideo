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
 * The "developers" collection of methods.
 * Typical usage is:
 *  <code>
 *   $apigeeService = new Google_Service_Apigee(...);
 *   $developers = $apigeeService->developers;
 *  </code>
 */
class Google_Service_Apigee_Resource_OrganizationsDevelopers extends Google_Service_Resource
{
  /**
   * Updates or creates developer attributes.This API replaces the current list of
   * attributes with the attributes specified in the request body. This lets you
   * update existing attributes, add new attributes, or delete existing attributes
   * by omitting them from the request body. the attribute limit is 18. Core
   * Persistence Services caching minimum: OAuth access tokens and Key Management
   * Service (KMS) entities (Apps, Developers, API Products) are cached for 180
   * seconds. Any custom attributes associated with entities also get cached for
   * at least 180 seconds after entity is accessed during runtime. This also means
   * the ExpiresIn element on the OAuthV2 policy won't be able to expire an access
   * token in less than 180 seconds. (developers.attributes)
   *
   * @param string $parent Required. The parent developer for which attributes are
   * being updated. Must be of the form
   * `organizations/{org}/developers/{developer}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1Attributes $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Attributes
   */
  public function attributes($parent, Google_Service_Apigee_GoogleCloudApigeeV1Attributes $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('attributes', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Attributes");
  }
  /**
   * Creates a profile for a developer in an organization. Once created, the
   * developer can register an app and receive an API key. The developer is always
   * created with a status of active. To set the status explicitly, use
   * SetDeveloperStatus (developers.create)
   *
   * @param string $parent Required. The parent organization name under which the
   * Developer will be created. Must be of the form `organizations/{org}`.
   * @param Google_Service_Apigee_GoogleCloudApigeeV1Developer $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Developer
   */
  public function create($parent, Google_Service_Apigee_GoogleCloudApigeeV1Developer $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Developer");
  }
  /**
   * Deletes a developer from an organization. All apps and API keys associated
   * with the developer are also removed from the organization. All times in the
   * response are UNIX times. (developers.delete)
   *
   * @param string $name Required. The name of the Developer to be deleted. Must
   * be of the form `organizations/{org}/developers/{developer}`
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Developer
   */
  public function delete($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Developer");
  }
  /**
   * Returns the profile for a developer by email address or ID. All time values
   * are UNIX time values. The profile includes the developer's email address, ID,
   * name, and other information. Apigee recommends using the developer email in
   * the API call. Developer ID is generated internally and is not guaranteed to
   * stay the same over time. For example, Apigee could change the format or
   * length of this variable. (developers.get)
   *
   * @param string $name Required. The name of the Developer to be get. Must be of
   * the form `organizations/{org}/developers/{developer}`
   * @param array $optParams Optional parameters.
   *
   * @opt_param string action Status to set active/inactive
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Developer
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Developer");
  }
  /**
   * Lists all developers in an organization by email address. This call does not
   * list any company developers who are a part of the designated organization.
   * (developers.listOrganizationsDevelopers)
   *
   * @param string $parent Required. The parent organization name. Must be of the
   * form `organizations/{org}`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string startKey Lets you return a list of developers starting with
   * a specific developer in the list.
   * @opt_param bool expand For Verbose response
   * @opt_param string ids Optional. Filtery by id, accepts list of ids with comma
   * seperation.
   * @opt_param bool includeCompany Optional. Filter to incude company details in
   * the response.
   * @opt_param string count Enter the number of developers you want returned in
   * the API call. The limit is 1000.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1ListOfDevelopersResponse
   */
  public function listOrganizationsDevelopers($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1ListOfDevelopersResponse");
  }
  /**
   * Sets a developer's status to active or inactive for a specific organization
   * Run this API for each organization where you want to change the developer's
   * status. By default, the status of a developer is set to active. Admins with
   * proper permissions (such as Organization Administrator) can change a
   * developer's status using this API call. If you set a developer's status to
   * inactive, the API keys assigned to the developer's apps are no longer valid
   * even though keys continue to show a status of "Approved" (in strikethrough
   * text in the management UI). Inactive developers, however, can still log into
   * the developer portal and create apps. The new keys that get created just
   * won't work.Apigee recommends using the developer email in the API call.
   * Developer ID is generated internally and is not guaranteed to stay the same
   * over time. For example, Apigee could change the format or length of this
   * variable. The HTTP status code for success is: 204 No Content.
   * (developers.setDeveloperStatus)
   *
   * @param string $name Required. The name of the Developer to be deleted. Must
   * be of the form `organizations/{org}/developers/{developer}`
   * @param array $optParams Optional parameters.
   *
   * @opt_param string action Status to set active/inactive
   * @return Google_Service_Apigee_GoogleProtobufEmpty
   */
  public function setDeveloperStatus($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('setDeveloperStatus', array($params), "Google_Service_Apigee_GoogleProtobufEmpty");
  }
  /**
   * Update an existing developer profile. To add new values or update existing
   * values, submit the new or updated portion of the developer profile along with
   * the rest of the developer profile, even if no values are changing. To delete
   * attributes from a developer profile, submit the entire profile without the
   * attributes that you want to delete. Apigee recommends using the developer
   * email in the API call. Developer ID is generated internally and is not
   * guaranteed to stay the same over time. For example, Apigee could change the
   * format or length of this variable. the custom attribute limit is 18. Core
   * Persistence Services caching minimum: OAuth access tokens and Key Management
   * Service (KMS) entities (Apps, Developers, API Products) are cached for 180
   * seconds (current default). Any custom attributes associated with entities
   * also get cached for at least 180 seconds after entity is accessed during
   * runtime. This also means the ExpiresIn element on the OAuthV2 policy won't be
   * able to expire an access token in less than 180 seconds. (developers.update)
   *
   * @param string $name Required. The name of the Developer to be updated. Must
   * be of the form `organizations/{org}/developers/{developer}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1Developer $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Developer
   */
  public function update($name, Google_Service_Apigee_GoogleCloudApigeeV1Developer $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Developer");
  }
}
