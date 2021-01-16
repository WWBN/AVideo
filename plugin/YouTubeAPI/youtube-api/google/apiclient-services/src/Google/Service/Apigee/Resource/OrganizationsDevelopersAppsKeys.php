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
 * The "keys" collection of methods.
 * Typical usage is:
 *  <code>
 *   $apigeeService = new Google_Service_Apigee(...);
 *   $keys = $apigeeService->keys;
 *  </code>
 */
class Google_Service_Apigee_Resource_OrganizationsDevelopersAppsKeys extends Google_Service_Resource
{
  /**
   * Creates a custom consumer key and secret for a developer app. This is
   * particularly useful if you want to migrate existing consumer keys/secrets to
   * Edge from another system. Be aware of the following size limits on API keys.
   * By staying within these limits, you help avoid service disruptions (2KB each
   * for Consumer Key and Secret). After creating the consumer key and secret,
   * associate the key with an API product using the API UpdateDeveloperAppKey If
   * a consumer key and secret already exist, you can either keep them or delete
   * them with this API DeleteKeyFromDeveloperApp Consumer keys and secrets can
   * contain letters, numbers, underscores, and hyphens. No other special
   * characters are allowed. (keys.create)
   *
   * @param string $parent Parent of a developer app key in the form
   * `organizations/{org}/developers/{developer}/apps`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey
   */
  public function create($parent, Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey");
  }
  /**
   * Deletes a consumer key that belongs to an app, and removes all API products
   * associated with the app. Once deleted, the consumer key cannot be used to
   * access any APIs. Note: After you delete a consumer key, you may want to: 1.
   * Create a new consumer key and secret for the developer app, and subsequently
   * add an API product to the key. 2. Delete the developer app, if it is no
   * longer required. (keys.delete)
   *
   * @param string $name Resource name of a developer app key
   * `organizations/{org}/developers/{developer}/apps/{app}/keys/{key}`
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
   * Returns details for a consumer key for a developer app, including the key and
   * secret value, associated API products, and other information. All times are
   * displayed as UNIX times. (keys.get)
   *
   * @param string $name Resource name of a developer app key
   * `organizations/{org}/developers/{developer}/apps/{app}/keys/{key}`
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey");
  }
  /**
   * Updates the scope of an app. Note that this API sets the scopes element under
   * the apiProducts element in the attributes of the app.
   * (keys.replaceDeveloperAppKey)
   *
   * @param string $name Resource name of a company app key
   * `organizations/{org}/developers/{developer}/apps/{app}/keys/{key}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey
   */
  public function replaceDeveloperAppKey($name, Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('replaceDeveloperAppKey', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey");
  }
  /**
   * Adds an API product to a developer app key, enabling the app that holds the
   * key to access the API resources bundled in the API product. You can also use
   * this API to add attributes to the key. Use this API to add a new API product
   * to an existing app. After adding the API product, you can use the same key to
   * access all API products associated with the app. You must include all
   * existing attributes, whether or not you are updating them, as well as any new
   * attributes that you are adding. (keys.updateDeveloperAppKey)
   *
   * @param string $name Resource name of a company app key
   * `organizations/{org}/developers/{developer}/apps/{app}/keys/{key}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string action Set the action to approve or revoke.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey
   */
  public function updateDeveloperAppKey($name, Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('updateDeveloperAppKey', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1DeveloperAppKey");
  }
}
