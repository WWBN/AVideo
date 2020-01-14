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
 * The "environments" collection of methods.
 * Typical usage is:
 *  <code>
 *   $tagmanagerService = new Google_Service_TagManager(...);
 *   $environments = $tagmanagerService->environments;
 *  </code>
 */
class Google_Service_TagManager_Resource_AccountsContainersEnvironments extends Google_Service_Resource
{
  /**
   * Creates a GTM Environment. (environments.create)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param Google_Service_TagManager_Environment $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_Environment
   */
  public function create($accountId, $containerId, Google_Service_TagManager_Environment $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_TagManager_Environment");
  }
  /**
   * Deletes a GTM Environment. (environments.delete)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $environmentId The GTM Environment ID.
   * @param array $optParams Optional parameters.
   */
  public function delete($accountId, $containerId, $environmentId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'environmentId' => $environmentId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }
  /**
   * Gets a GTM Environment. (environments.get)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $environmentId The GTM Environment ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_Environment
   */
  public function get($accountId, $containerId, $environmentId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'environmentId' => $environmentId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_Environment");
  }
  /**
   * Lists all GTM Environments of a GTM Container.
   * (environments.listAccountsContainersEnvironments)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_ListEnvironmentsResponse
   */
  public function listAccountsContainersEnvironments($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListEnvironmentsResponse");
  }
  /**
   * Updates a GTM Environment. This method supports patch semantics.
   * (environments.patch)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $environmentId The GTM Environment ID.
   * @param Google_Service_TagManager_Environment $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string fingerprint When provided, this fingerprint must match the
   * fingerprint of the environment in storage.
   * @return Google_Service_TagManager_Environment
   */
  public function patch($accountId, $containerId, $environmentId, Google_Service_TagManager_Environment $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'environmentId' => $environmentId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_TagManager_Environment");
  }
  /**
   * Updates a GTM Environment. (environments.update)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $environmentId The GTM Environment ID.
   * @param Google_Service_TagManager_Environment $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string fingerprint When provided, this fingerprint must match the
   * fingerprint of the environment in storage.
   * @return Google_Service_TagManager_Environment
   */
  public function update($accountId, $containerId, $environmentId, Google_Service_TagManager_Environment $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'environmentId' => $environmentId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_Environment");
  }
}
