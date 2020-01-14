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
 * The "move_folders" collection of methods.
 * Typical usage is:
 *  <code>
 *   $tagmanagerService = new Google_Service_TagManager(...);
 *   $move_folders = $tagmanagerService->move_folders;
 *  </code>
 */
class Google_Service_TagManager_Resource_AccountsContainersMoveFolders extends Google_Service_Resource
{
  /**
   * Moves entities to a GTM Folder. (move_folders.update)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $folderId The GTM Folder ID.
   * @param Google_Service_TagManager_Folder $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string tagId The tags to be moved to the folder.
   * @opt_param string triggerId The triggers to be moved to the folder.
   * @opt_param string variableId The variables to be moved to the folder.
   */
  public function update($accountId, $containerId, $folderId, Google_Service_TagManager_Folder $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'folderId' => $folderId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params));
  }
}
