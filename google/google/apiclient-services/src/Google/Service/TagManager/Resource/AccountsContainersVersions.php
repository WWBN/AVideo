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
 * The "versions" collection of methods.
 * Typical usage is:
 *  <code>
 *   $tagmanagerService = new Google_Service_TagManager(...);
 *   $versions = $tagmanagerService->versions;
 *  </code>
 */
class Google_Service_TagManager_Resource_AccountsContainersVersions extends Google_Service_Resource
{
  /**
   * Creates a Container Version. (versions.create)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param Google_Service_TagManager_CreateContainerVersionRequestVersionOptions $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_CreateContainerVersionResponse
   */
  public function create($accountId, $containerId, Google_Service_TagManager_CreateContainerVersionRequestVersionOptions $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "Google_Service_TagManager_CreateContainerVersionResponse");
  }
  /**
   * Deletes a Container Version. (versions.delete)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $containerVersionId The GTM Container Version ID.
   * @param array $optParams Optional parameters.
   */
  public function delete($accountId, $containerId, $containerVersionId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'containerVersionId' => $containerVersionId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }
  /**
   * Gets a Container Version. (versions.get)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $containerVersionId The GTM Container Version ID. Specify
   * published to retrieve the currently published version.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_ContainerVersion
   */
  public function get($accountId, $containerId, $containerVersionId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'containerVersionId' => $containerVersionId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_TagManager_ContainerVersion");
  }
  /**
   * Lists all Container Versions of a GTM Container.
   * (versions.listAccountsContainersVersions)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool headers Retrieve headers only when true.
   * @opt_param bool includeDeleted Also retrieve deleted (archived) versions when
   * true.
   * @return Google_Service_TagManager_ListContainerVersionsResponse
   */
  public function listAccountsContainersVersions($accountId, $containerId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_TagManager_ListContainerVersionsResponse");
  }
  /**
   * Publishes a Container Version. (versions.publish)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $containerVersionId The GTM Container Version ID.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string fingerprint When provided, this fingerprint must match the
   * fingerprint of the container version in storage.
   * @return Google_Service_TagManager_PublishContainerVersionResponse
   */
  public function publish($accountId, $containerId, $containerVersionId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'containerVersionId' => $containerVersionId);
    $params = array_merge($params, $optParams);
    return $this->call('publish', array($params), "Google_Service_TagManager_PublishContainerVersionResponse");
  }
  /**
   * Restores a Container Version. This will overwrite the container's current
   * configuration (including its variables, triggers and tags). The operation
   * will not have any effect on the version that is being served (i.e. the
   * published version). (versions.restore)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $containerVersionId The GTM Container Version ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_ContainerVersion
   */
  public function restore($accountId, $containerId, $containerVersionId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'containerVersionId' => $containerVersionId);
    $params = array_merge($params, $optParams);
    return $this->call('restore', array($params), "Google_Service_TagManager_ContainerVersion");
  }
  /**
   * Undeletes a Container Version. (versions.undelete)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $containerVersionId The GTM Container Version ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_TagManager_ContainerVersion
   */
  public function undelete($accountId, $containerId, $containerVersionId, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'containerVersionId' => $containerVersionId);
    $params = array_merge($params, $optParams);
    return $this->call('undelete', array($params), "Google_Service_TagManager_ContainerVersion");
  }
  /**
   * Updates a Container Version. (versions.update)
   *
   * @param string $accountId The GTM Account ID.
   * @param string $containerId The GTM Container ID.
   * @param string $containerVersionId The GTM Container Version ID.
   * @param Google_Service_TagManager_ContainerVersion $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string fingerprint When provided, this fingerprint must match the
   * fingerprint of the container version in storage.
   * @return Google_Service_TagManager_ContainerVersion
   */
  public function update($accountId, $containerId, $containerVersionId, Google_Service_TagManager_ContainerVersion $postBody, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'containerId' => $containerId, 'containerVersionId' => $containerVersionId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_TagManager_ContainerVersion");
  }
}
