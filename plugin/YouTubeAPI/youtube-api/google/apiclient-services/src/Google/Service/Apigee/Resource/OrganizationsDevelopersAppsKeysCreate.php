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
 * The "create" collection of methods.
 * Typical usage is:
 *  <code>
 *   $apigeeService = new Google_Service_Apigee(...);
 *   $create = $apigeeService->create;
 *  </code>
 */
class Google_Service_Apigee_Resource_OrganizationsDevelopersAppsKeysCreate extends Google_Service_Resource
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
   * characters are allowed. (create.create)
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
}
