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
 * The "variables" collection of methods.
 * Typical usage is:
 *  <code>
 *   $tagmanagerService = new Google_Service_TagManager(...);
 *   $variables = $tagmanagerService->variables;
 *  </code>
 */
class Google_Service_TagManager_Resource_AccountsContainersVariables extends Google_Service_Resource
{
  /**
   * Creates a GTM Variable. (variables.create)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param Google_Service_TagManager_Variable $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_Variable
   */
  public function create($accountId, $containerId, Google_Service_TagManager_Variable $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_TagManager_Variable");
  }
  /**
   * Deletes a GTM Variable. (variables.delete)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $variableId The GTM Variable ID.
   * @param array $optParams Optional parameters.
   */
  public function delete($accountId, $containerId, $variableId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'variableId' => $variableId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }
  /**
   * Gets a GTM Variable. (variables.get)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $variableId The GTM Variable ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_Variable
   */
  public function get($accountId, $containerId, $variableId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'variableId' => $variableId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_Variable");
  }
  /**
   * Lists all GTM Variables of a Container.
   * (variables.listAccountsContainersVariables)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_ListVariablesResponse
   */
  public function listAccountsContainersVariables($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListVariablesResponse");
  }
  /**
   * Updates a GTM Variable. (variables.update)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $variableId The GTM Variable ID.
   * @param Google_Service_TagManager_Variable $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string fingerprint When provided, this fingerprint must match the
   * fingerprint of the variable in storage.
   * @return Google_Service_TagManager_Variable
   */
  public function update($accountId, $containerId, $variableId, Google_Service_TagManager_Variable $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'variableId' => $variableId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_Variable");
  }
}
