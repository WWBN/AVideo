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
 * The "organizations" collection of methods.
 * Typical usage is:
 *  <code>
 *   $cloudresourcemanagerService = new Google_Service_CloudResourceManager(...);
 *   $organizations = $cloudresourcemanagerService->organizations;
 *  </code>
 */
class Google_Service_CloudResourceManager_Resource_Organizations extends Google_Service_Resource
{
  /**
   * Fetches an Organization resource identified by the specified resource name.
   * (organizations.get)
   *
   * @param string $name The resource name of the Organization to fetch, e.g.
   * "organizations/1234".
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudResourceManager_Organization
   */
  public function get($name, $optParams = array())
  {
    $params = array('name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_CloudResourceManager_Organization");
  }
  /**
   * Gets the access control policy for an Organization resource. May be empty if
   * no such policy or resource exists. The `resource` field should be the
   * organization's resource name, e.g. "organizations/123".
   * (organizations.getIamPolicy)
   *
   * @param string $resource REQUIRED: The resource for which the policy is being
   * requested. See the operation documentation for the appropriate value for this
   * field.
   * @param Google_Service_CloudResourceManager_GetIamPolicyRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudResourceManager_Policy
   */
  public function getIamPolicy($resource, Google_Service_CloudResourceManager_GetIamPolicyRequest $postBody, $optParams = array())
  {
    $params = array('resource' => $resource, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('getIamPolicy', array($params), "Google_Service_CloudResourceManager_Policy");
  }
  /**
   * Searches Organization resources that are visible to the user and satisfy the
   * specified filter. This method returns Organizations in an unspecified order.
   * New Organizations do not necessarily appear at the end of the results.
   * (organizations.search)
   *
   * @param Google_Service_CloudResourceManager_SearchOrganizationsRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudResourceManager_SearchOrganizationsResponse
   */
  public function search(Google_Service_CloudResourceManager_SearchOrganizationsRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('search', array($params), "Google_Service_CloudResourceManager_SearchOrganizationsResponse");
  }
  /**
   * Sets the access control policy on an Organization resource. Replaces any
   * existing policy. The `resource` field should be the organization's resource
   * name, e.g. "organizations/123". (organizations.setIamPolicy)
   *
   * @param string $resource REQUIRED: The resource for which the policy is being
   * specified. See the operation documentation for the appropriate value for this
   * field.
   * @param Google_Service_CloudResourceManager_SetIamPolicyRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudResourceManager_Policy
   */
  public function setIamPolicy($resource, Google_Service_CloudResourceManager_SetIamPolicyRequest $postBody, $optParams = array())
  {
    $params = array('resource' => $resource, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('setIamPolicy', array($params), "Google_Service_CloudResourceManager_Policy");
  }
  /**
   * Returns permissions that a caller has on the specified Organization. The
   * `resource` field should be the organization's resource name, e.g.
   * "organizations/123". (organizations.testIamPermissions)
   *
   * @param string $resource REQUIRED: The resource for which the policy detail is
   * being requested. See the operation documentation for the appropriate value
   * for this field.
   * @param Google_Service_CloudResourceManager_TestIamPermissionsRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudResourceManager_TestIamPermissionsResponse
   */
  public function testIamPermissions($resource, Google_Service_CloudResourceManager_TestIamPermissionsRequest $postBody, $optParams = array())
  {
    $params = array('resource' => $resource, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('testIamPermissions', array($params), "Google_Service_CloudResourceManager_TestIamPermissionsResponse");
  }
}
