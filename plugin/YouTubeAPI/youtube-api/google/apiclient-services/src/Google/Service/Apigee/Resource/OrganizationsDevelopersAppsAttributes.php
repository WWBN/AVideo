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
class Google_Service_Apigee_Resource_OrganizationsDevelopersAppsAttributes extends Google_Service_Resource
{
  /**
   * Deletes an app attribute. (attributes.delete)
   *
   * @param string $name Required. Developer App Attribute name of the form:   `or
   * ganizations/{organization_id}/developers/{developer_id}/apps/{app_name}/attri
   * butes/{attribute_name}`
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
   * Returns the value of an app attribute. (attributes.get)
   *
   * @param string $name Required. Developer App Attribute name of the form:   `or
   * ganizations/{organization_id}/developers/{developer_id}/apps/{app_name}/attri
   * butes/{attribute_name}`
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
   * Returns a list of all app attributes.
   * (attributes.listOrganizationsDevelopersAppsAttributes)
   *
   * @param string $parent Required. The parent organization name. Must be of the
   * form:
   * `organizations/{organization_id}/developers/{developer_id}/apps/{app_name}`
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Attributes
   */
  public function listOrganizationsDevelopersAppsAttributes($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Attributes");
  }
  /**
   * Updates an app attribute (attributes.updateDeveloperAppAttribute)
   *
   * @param string $name Required. Developer App Attribute name of the form:   `or
   * ganizations/{organization_id}/developers/{developer_id}/apps/{app_name}/attri
   * butes/{attribute_name}`
   * @param Google_Service_Apigee_GoogleCloudApigeeV1Attribute $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Apigee_GoogleCloudApigeeV1Attribute
   */
  public function updateDeveloperAppAttribute($name, Google_Service_Apigee_GoogleCloudApigeeV1Attribute $postBody, $optParams = array())
  {
    $params = array('name' => $name, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('updateDeveloperAppAttribute', array($params), "Google_Service_Apigee_GoogleCloudApigeeV1Attribute");
  }
}
