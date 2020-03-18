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
 * The "collections" collection of methods.
 * Typical usage is:
 *  <code>
 *   $androidenterpriseService = new Google_Service_AndroidEnterprise(...);
 *   $collections = $androidenterpriseService->collections;
 *  </code>
 */
class Google_Service_AndroidEnterprise_Resource_Collections extends Google_Service_Resource
{
  /**
   * Deletes a collection. (collections.delete)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $collectionId The ID of the collection.
   * @param array $optParams Optional parameters.
   */
  public function delete($enterpriseId, $collectionId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }
  /**
   * Retrieves the details of a collection. (collections.get)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $collectionId The ID of the collection.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_Collection
   */
  public function get($enterpriseId, $collectionId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_AndroidEnterprise_Collection");
  }
  /**
   * Creates a new collection. (collections.insert)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param Google_Service_AndroidEnterprise_Collection $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_Collection
   */
  public function insert($enterpriseId, Google_Service_AndroidEnterprise_Collection $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_AndroidEnterprise_Collection");
  }
  /**
   * Retrieves the IDs of all the collections for an enterprise.
   * (collections.listCollections)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_CollectionsListResponse
   */
  public function listCollections($enterpriseId, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_AndroidEnterprise_CollectionsListResponse");
  }
  /**
   * Updates a collection. This method supports patch semantics.
   * (collections.patch)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $collectionId The ID of the collection.
   * @param Google_Service_AndroidEnterprise_Collection $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_Collection
   */
  public function patch($enterpriseId, $collectionId, Google_Service_AndroidEnterprise_Collection $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_AndroidEnterprise_Collection");
  }
  /**
   * Updates a collection. (collections.update)
   *
   * @param string $enterpriseId The ID of the enterprise.
   * @param string $collectionId The ID of the collection.
   * @param Google_Service_AndroidEnterprise_Collection $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_AndroidEnterprise_Collection
   */
  public function update($enterpriseId, $collectionId, Google_Service_AndroidEnterprise_Collection $postBody, $optParams = array())
  {
    $params = array('enterpriseId' => $enterpriseId, 'collectionId' => $collectionId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_AndroidEnterprise_Collection");
  }
}
