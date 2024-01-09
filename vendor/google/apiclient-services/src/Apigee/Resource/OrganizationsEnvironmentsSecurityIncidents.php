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

namespace Google\Service\Apigee\Resource;

use Google\Service\Apigee\GoogleCloudApigeeV1ListSecurityIncidentsResponse;
use Google\Service\Apigee\GoogleCloudApigeeV1SecurityIncident;

/**
 * The "securityIncidents" collection of methods.
 * Typical usage is:
 *  <code>
 *   $apigeeService = new Google\Service\Apigee(...);
 *   $securityIncidents = $apigeeService->organizations_environments_securityIncidents;
 *  </code>
 */
class OrganizationsEnvironmentsSecurityIncidents extends \Google\Service\Resource
{
  /**
   * GetSecurityIncident gets the specified security incident. Returns NOT_FOUND
   * if security incident is not present for the specified organization and
   * environment. (securityIncidents.get)
   *
   * @param string $name Required. Security incident in the following format: `org
   * anizations/{org}/environments/{environment}/securityIncidents/{incident}'.
   * Example: organizations/testOrg/environments/testEnv/securityIncidents/1234-45
   * 67-890-111
   * @param array $optParams Optional parameters.
   * @return GoogleCloudApigeeV1SecurityIncident
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], GoogleCloudApigeeV1SecurityIncident::class);
  }
  /**
   * ListSecurityIncidents lists all the security incident associated with the
   * environment.
   * (securityIncidents.listOrganizationsEnvironmentsSecurityIncidents)
   *
   * @param string $parent Required. For a specific organization, list of all the
   * security incidents. Format: `organizations/{org}/environments/{environment}`
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter The filter expression to be used to get the list of
   * security incidents, where filtering can be done on API Proxies. Example:
   * filter = "api_proxy = /", "first_detected_time >", "last_detected_time <"
   * @opt_param int pageSize The maximum number of incidents to return. The
   * service may return fewer than this value. If unspecified, at most 50
   * incidents will be returned.
   * @opt_param string pageToken A page token, received from a previous
   * `ListSecurityIncident` call. Provide this to retrieve the subsequent page.
   * @return GoogleCloudApigeeV1ListSecurityIncidentsResponse
   */
  public function listOrganizationsEnvironmentsSecurityIncidents($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], GoogleCloudApigeeV1ListSecurityIncidentsResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(OrganizationsEnvironmentsSecurityIncidents::class, 'Google_Service_Apigee_Resource_OrganizationsEnvironmentsSecurityIncidents');
