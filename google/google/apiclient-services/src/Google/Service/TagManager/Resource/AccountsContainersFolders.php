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
 * The "folders" collection of methods.
 * Typical usage is:
 *  <code>
 *   $tagmanagerService = new Google_Service_TagManager(...);
 *   $folders = $tagmanagerService->folders;
 *  </code>
 */
class Google_Service_TagManager_Resource_AccountsContainersFolders extends Google_Service_Resource
{
  /**
   * Creates a GTM Folder. (folders.create)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param Google_Service_TagManager_Folder $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_Folder
   */
  public function create($accountId, $containerId, Google_Service_TagManager_Folder $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_TagManager_Folder");
  }
  /**
   * Deletes a GTM Folder. (folders.delete)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $folderId The GTM Folder ID.
   * @param array $optParams Optional parameters.
   */
  public function delete($accountId, $containerId, $folderId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'folderId' => $folderId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }
  /**
   * Gets a GTM Folder. (folders.get)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $folderId The GTM Folder ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_Folder
   */
  public function get($accountId, $containerId, $folderId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'folderId' => $folderId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_Folder");
  }
  /**
   * Lists all GTM Folders of a Container. (folders.listAccountsContainersFolders)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_ListFoldersResponse
   */
  public function listAccountsContainersFolders($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListFoldersResponse");
  }
  /**
   * Updates a GTM Folder. (folders.update)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $folderId The GTM Folder ID.
   * @param Google_Service_TagManager_Folder $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string fingerprint When provided, this fingerprint must match the
   * fingerprint of the folder in storage.
   * @return Google_Service_TagManager_Folder
   */
  public function update($accountId, $containerId, $folderId, Google_Service_TagManager_Folder $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'folderId' => $folderId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_Folder");
  }
}
