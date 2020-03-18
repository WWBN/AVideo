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
 * The "triggers" collection of methods.
 * Typical usage is:
 *  <code>
 *   $tagmanagerService = new Google_Service_TagManager(...);
 *   $triggers = $tagmanagerService->triggers;
 *  </code>
 */
class Google_Service_TagManager_Resource_AccountsContainersTriggers extends Google_Service_Resource
{
  /**
   * Creates a GTM Trigger. (triggers.create)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param Google_Service_TagManager_Trigger $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_Trigger
   */
  public function create($accountId, $containerId, Google_Service_TagManager_Trigger $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_TagManager_Trigger");
  }
  /**
   * Deletes a GTM Trigger. (triggers.delete)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $triggerId The GTM Trigger ID.
   * @param array $optParams Optional parameters.
   */
  public function delete($accountId, $containerId, $triggerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'triggerId' => $triggerId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }
  /**
   * Gets a GTM Trigger. (triggers.get)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $triggerId The GTM Trigger ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_Trigger
   */
  public function get($accountId, $containerId, $triggerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'triggerId' => $triggerId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_Trigger");
  }
  /**
   * Lists all GTM Triggers of a Container.
   * (triggers.listAccountsContainersTriggers)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_ListTriggersResponse
   */
  public function listAccountsContainersTriggers($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListTriggersResponse");
  }
  /**
   * Updates a GTM Trigger. (triggers.update)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $triggerId The GTM Trigger ID.
   * @param Google_Service_TagManager_Trigger $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string fingerprint When provided, this fingerprint must match the
   * fingerprint of the trigger in storage.
   * @return Google_Service_TagManager_Trigger
   */
  public function update($accountId, $containerId, $triggerId, Google_Service_TagManager_Trigger $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'triggerId' => $triggerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_Trigger");
  }
}
