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
 * The "apps" collection of methods.
 * Typical usage is:
 *  <code>
 *   $apigeeService = new Google_Service_Apigee(...);
 *   $apps = $apigeeService->apps;
 *  </code>
 */
class Google_Service_Apigee_Resource_OrganizationsDevelopersApps extends Google_Service_Resource
{
  /**
   * Updates or creates app attributes. This API replaces the current list of
   * attributes with the attributes specified in the request body. This lets you
   * update existing attributes, add new attributes, or delete existing attributes
   * by omitting them from the request body. (apps.attributes)
   *
   * @param string $name Required. Developer App Attribute name of the form:
   * `organizations/{organization_id}/developers/{developer_id}/apps/{app_name}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1Attributes $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Attributes
   */
  public function attributes($name, Google_Service_Apigee_GoogleCloudApigeeV1Attributes $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('attributes', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Attributes");
  }
  /**
   * Creates an app associated with a developer, associates the app with an API
   * product, and auto-generates an API key for the app to use in calls to API
   * proxies inside the API product. The name is the unique ID of the app that you
   * can use in management API calls. The DisplayName (set with an attribute) is
   * what appears in the management UI. If you don't provide a DisplayName, the
   * name is used. The keyExpiresIn property sets the expiration on the API key.
   * If you don't set this, or set the value to -1, they API key never expires.
   * (apps.create)
   *
   * @param string $parent Required. The parent organization name under which the
   * Developer App will be created. Must be of the form:
   * `organizations/{organization_id}/developers/{developer_id}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp
   */
  public function create($parent, Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp");
  }
  /**
   * Deletes a developer app. This API returns the developer app that was deleted.
   * (apps.delete)
   *
   * @param string $name Required. Developer App name of the form:
   * `organizations/{organization_id}/developers/{developer_id}/apps/{app_name}`
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp
   */
  public function delete($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp");
  }
  /**
   * (2) Create new developer KeyPairs Generates a new consumer key and consumer
   * secret for the named developer app. Rather than replacing an existing key,
   * this API call generates a new key. For example, if you're using API key
   * rotation, you can generate new keys whose expiration overlaps keys that will
   * be out of rotation when they expire. You might also generate a new key/secret
   * if the security of the original key/secret is compromised. After using this
   * API, multiple key pairs will be associated with a single app. Each key pair
   * has an independent status (revoked or approved) and an independent expiry
   * time. Any non-expired, approved key can be used in an API call. The
   * keyExpiresIn value is in milliseconds. A value of -1 means the key/secret
   * pair never expire. (apps.generateKeyPairOrUpdateDeveloperAppStatus)
   *
   * @param string $name Required. Developer App name of the form:
   * `organizations/{organization_id}/developers/{developer_id}/apps/{app_name}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string action Set the action to approve or revoke.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp
   */
  public function generateKeyPairOrUpdateDeveloperAppStatus($name, Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('generateKeyPairOrUpdateDeveloperAppStatus', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp");
  }
  /**
   * Get the profile of a specific developer app. All times in the response are
   * UNIX times. Note that the response contains a top-level attribute named
   * accessType that is no longer used by Apigee. (apps.get)
   *
   * @param string $name Required. Developer App name of the form:
   * `organizations/{organization_id}/developers/{developer_id}/apps/{app_name}`
   * @param array $optParams Optional parameters.
   *
   * @opt_param string query Query.
   * @opt_param string entity Entity.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp");
  }
  /**
   * Lists all apps created by a developer in an organization, and optionally
   * provides an expanded view of the apps. All time values in the response are
   * UNIX times. You can specify either the developer's email address or Edge ID.
   * (apps.listOrganizationsDevelopersApps)
   *
   * @param string $parent Required. The parent organization name. Must be of the
   * form:   `organizations/{organization_id}/developers/{developer_id}`
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool shallowExpand Optional. Set to true to expand the results in
   * shallow.
   * @opt_param string startKey To filter the keys that are returned, enter the
   * name of a company app that the list will start with.
   * @opt_param string count Limits the list to the number you specify.
   * @opt_param bool expand Optional. Set to true to expand the results. This
   * query parameter does not work if you use the count or startKey query
   * parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1ListDeveloperAppsResponse
   */
  public function listOrganizationsDevelopersApps($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1ListDeveloperAppsResponse");
  }
  /**
   * Updates a developer app. You can also add an app to an API product with this
   * call, which automatically generates an API key for the app to use when
   * calling APIs in the product. (If you want to use an existing API key for
   * another API product as well, see Add API Product to Key.) Be sure to include
   * all existing attributes in the request body. Note that you cannot update the
   * scopes associated with the app by using this API. Instead, use "Update the
   * Scope of an App". The app name is the primary key used by Edge to identify
   * the app. Therefore, you cannot change the app name after creating it.
   * (apps.update)
   *
   * @param string $name Required. Developer App name of the form:
   * `organizations/{organization_id}/developers/{developer_id}/apps/{app_name}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp
   */
  public function update($name, Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1DeveloperApp");
  }
}
