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
 * The "collectionviewers" collection of methods.
 * Typical usage is:
 *  <code>
 *   $androidenterpriseService = new Google_Service_AndroidEnterprise(...);
 *   $collectionviewers = $androidenterpriseService->collectionviewers;
 *  </code>
 */
class Google_Service_AndroidEnterprise_Resource_Collectionviewers extends Google_Service_Resource
{
  /**
   * Removes the user from the list of those specifically allowed to see the
   * collection. If the collection's visibility is set to viewersOnly then only
   * such users will see the collection. (collectionviewers.delete)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $collectionId The ID of the collection.
   * @param string $userId The ID of the user.
   * @param array $optParams Optional parameters.
   */
  public function delete($enterpriseId, $collectionId, $userId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }
  /**
   * Retrieves the ID of the user if they have been specifically allowed to see
   * the collection. If the collection's visibility is set to viewersOnly then
   * only these users will see the collection. (collectionviewers.get)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $collectionId The ID of the collection.
   * @param string $userId The ID of the user.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_User
   */
  public function get($enterpriseId, $collectionId, $userId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidEnterprise_User");
  }
  /**
   * Retrieves the IDs of the users who have been specifically allowed to see the
   * collection. If the collection's visibility is set to viewersOnly then only
   * these users will see the collection.
   * (collectionviewers.listCollectionviewers)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $collectionId The ID of the collection.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_CollectionViewersListResponse
   */
  public function listCollectionviewers($enterpriseId, $collectionId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidEnterprise_CollectionViewersListResponse");
  }
  /**
   * Adds the user to the list of those specifically allowed to see the
   * collection. If the collection's visibility is set to viewersOnly then only
   * such users will see the collection. This method supports patch semantics.
   * (collectionviewers.patch)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $collectionId The ID of the collection.
   * @param string $userId The ID of the user.
   * @param Google_Service_AndroidEnterprise_User $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_User
   */
  public function patch($enterpriseId, $collectionId, $userId, Google_Service_AndroidEnterprise_User $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId, 'userId' => $userId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_AndroidEnterprise_User");
  }
  /**
   * Adds the user to the list of those specifically allowed to see the
   * collection. If the collection's visibility is set to viewersOnly then only
   * such users will see the collection. (collectionviewers.update)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $collectionId The ID of the collection.
   * @param string $userId The ID of the user.
   * @param Google_Service_AndroidEnterprise_User $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_User
   */
  public function update($enterpriseId, $collectionId, $userId, Google_Service_AndroidEnterprise_User $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId, 'userId' => $userId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_AndroidEnterprise_User");
  }
}
