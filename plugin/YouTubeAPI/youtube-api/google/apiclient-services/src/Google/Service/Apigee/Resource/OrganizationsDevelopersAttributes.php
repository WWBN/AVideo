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
 * The "attributes" collection of methods.
 * Typical usage is:
 *  <code>
 *   $apigeeService = new Google_Service_Apigee(...);
 *   $attributes = $apigeeService->attributes;
 *  </code>
 */
class Google_Service_Apigee_Resource_OrganizationsDevelopersAttributes extends Google_Service_Resource
{
  /**
   * Deletes an attribute of a Developer resource Apigee recommends using the
   * developer email in the API call. Developer ID is generated internally and is
   * not guaranteed to stay the same over time. For example, Apigee could change
   * the format or length of this variable. (attributes.delete)
   *
   * @param string $name Required. The name of the attribute for a developer. Must
   * be of the form
   * `organizations/{org}/developers/{developer}/attributes/{attribute}`
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Attribute
   */
  public function delete($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Attribute");
  }
  /**
   * Get developer attributes. Apigee recommends using the developer email in the
   * API call. Developer ID is generated internally and is not guaranteed to stay
   * the same over time. For example, Apigee could change the format or length of
   * this variable. (attributes.get)
   *
   * @param string $name Required. The name of the attribute for a developer. Must
   * be of the form
   * `organizations/{org}/developers/{developer}/attributes/{attribute}`
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Attribute
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Attribute");
  }
  /**
   * Returns a list of all developer attributes. Apigee recommends using the
   * developer email in the API call. Developer ID is generated internally and is
   * not guaranteed to stay the same over time. For example, Apigee could change
   * the format or length of this variable.
   * (attributes.listOrganizationsDevelopersAttributes)
   *
   * @param string $parent Required. The parent developer for which attributes are
   * being listed. Must be of the form
   * `organizations/{org}/developers/{developer}`
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Attributes
   */
  public function listOrganizationsDevelopersAttributes($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Attributes");
  }
  /**
   * Update developer attribute. OAuth access tokens and Key Management Service
   * (KMS) entities (Apps, Developers, API Products) are cached for 180 seconds
   * (current default). Any custom attributes associated with entities also get
   * cached for at least 180 seconds after entity is accessed during runtime. This
   * also means the ExpiresIn element on the OAuthV2 policy won't be able to
   * expire an access token in less than 180 seconds. Apigee recommends using the
   * developer email in the API call. Developer ID is generated internally and is
   * not guaranteed to stay the same over time. For example, Apigee could change
   * the format or length of this variable. (attributes.updateDeveloperAttribute)
   *
   * @param string $name Required. The name of the attribute for a developer. Must
   * be of the form
   * `organizations/{org}/developers/{developer}/attributes/{attribute}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1Attribute $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Attribute
   */
  public function updateDeveloperAttribute($name, Google_Service_Apigee_GoogleCloudApigeeV1Attribute $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('updateDeveloperAttribute', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Attribute");
  }
}
