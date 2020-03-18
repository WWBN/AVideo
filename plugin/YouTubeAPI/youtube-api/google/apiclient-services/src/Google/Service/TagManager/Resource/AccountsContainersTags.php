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
 * The "tags" collection of methods.
 * Typical usage is:
 *  <code>
 *   $tagmanagerService = new Google_Service_TagManager(...);
 *   $tags = $tagmanagerService->tags;
 *  </code>
 */
class Google_Service_TagManager_Resource_AccountsContainersTags extends Google_Service_Resource
{
  /**
   * Creates a GTM Tag. (tags.create)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param Google_Service_TagManager_Tag $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_Tag
   */
  public function create($accountId, $containerId, Google_Service_TagManager_Tag $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_TagManager_Tag");
  }
  /**
   * Deletes a GTM Tag. (tags.delete)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $tagId The GTM Tag ID.
   * @param array $optParams Optional parameters.
   */
  public function delete($accountId, $containerId, $tagId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'tagId' => $tagId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }
  /**
   * Gets a GTM Tag. (tags.get)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $tagId The GTM Tag ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_Tag
   */
  public function get($accountId, $containerId, $tagId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'tagId' => $tagId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_Tag");
  }
  /**
   * Lists all GTM Tags of a Container. (tags.listAccountsContainersTags)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_ListTagsResponse
   */
  public function listAccountsContainersTags($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListTagsResponse");
  }
  /**
   * Updates a GTM Tag. (tags.update)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $tagId The GTM Tag ID.
   * @param Google_Service_TagManager_Tag $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string fingerprint When provided, this fingerprint must match the
   * fingerprint of the tag in storage.
   * @return Google_Service_TagManager_Tag
   */
  public function update($accountId, $containerId, $tagId, Google_Service_TagManager_Tag $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'tagId' => $tagId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_Tag");
  }
}
