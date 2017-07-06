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
 * The "containers" collection of methods.
 * Typical usage is:
 *  <code>
 *   $tagmanagerService = new Google_Service_TagManager(...);
 *   $containers = $tagmanagerService->containers;
 *  </code>
 */
class Google_Service_TagManager_Resource_AccountsContainers extends Google_Service_Resource
{
  /**
   * Creates a Container. (containers.create)
   *
   * @param string $accountId The GTM Account ID.
   * @param Google_Service_TagManager_Container $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_Container
   */
  public function create($accountId, Google_Service_TagManager_Container $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_TagManager_Container");
  }
  /**
   * Deletes a Container. (containers.delete)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param array $optParams Optional parameters.
   */
  public function delete($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }
  /**
   * Gets a Container. (containers.get)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_Container
   */
  public function get($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_Container");
  }
  /**
   * Lists all Containers that belongs to a GTM Account.
   * (containers.listAccountsContainers)
   *
   * @param string $accountId The GTM Account ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_ListContainersResponse
   */
  public function listAccountsContainers($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListContainersResponse");
  }
  /**
   * Updates a Container. (containers.update)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param Google_Service_TagManager_Container $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string fingerprint When provided, this fingerprint must match the
   * fingerprint of the container in storage.
   * @return Google_Service_TagManager_Container
   */
  public function update($accountId, $containerId, Google_Service_TagManager_Container $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_Container");
  }
}
