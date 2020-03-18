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
 * The "reauthorize_environments" collection of methods.
 * Typical usage is:
 *  <code>
 *   $tagmanagerService = new Google_Service_TagManager(...);
 *   $reauthorize_environments = $tagmanagerService->reauthorize_environments;
 *  </code>
 */
class Google_Service_TagManager_Resource_AccountsContainersReauthorizeEnvironments extends Google_Service_Resource
{
  /**
   * Re-generates the authorization code for a GTM Environment.
   * (reauthorize_environments.update)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $environmentId The GTM Environment ID.
   * @param Google_Service_TagManager_Environment $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_Environment
   */
  public function update($accountId, $containerId, $environmentId, Google_Service_TagManager_Environment $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'environmentId' => $environmentId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_Environment");
  }
}
