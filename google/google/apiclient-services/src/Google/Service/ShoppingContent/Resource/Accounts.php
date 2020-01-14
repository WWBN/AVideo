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
 * The "accounts" collection of methods.
 * Typical usage is:
 *  <code>
 *   $contentService = new Google_Service_ShoppingContent(...);
 *   $accounts = $contentService->accounts;
 *  </code>
 */
class Google_Service_ShoppingContent_Resource_Accounts extends Google_Service_Resource
{
  /**
   * Returns information about the authenticated user. (accounts.authinfo)
   *
   * @param array $optParams Optional parameters.
   * @return Google_Service_ShoppingContent_AccountsAuthInfoResponse
   */
  public function authinfo($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('authinfo', array($params), "Google_Service_ShoppingContent_AccountsAuthInfoResponse");
  }
  /**
   * Retrieves, inserts, updates, and deletes multiple Merchant Center
   * (sub-)accounts in a single request. (accounts.custombatch)
   *
   * @param Google_Service_ShoppingContent_AccountsCustomBatchRequest $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool dryRun Flag to run the request in dry-run mode.
   * @return Google_Service_ShoppingContent_AccountsCustomBatchResponse
   */
  public function custombatch(Google_Service_ShoppingContent_AccountsCustomBatchRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('custombatch', array($params), "Google_Service_ShoppingContent_AccountsCustomBatchResponse");
  }
  /**
   * Deletes a Merchant Center sub-account. This method can only be called for
   * multi-client accounts. (accounts.delete)
   *
   * @param string $merchantId The ID of the managing account.
   * @param string $accountId The ID of the account.
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool dryRun Flag to run the request in dry-run mode.
   */
  public function delete($merchantId, $accountId, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }
  /**
   * Retrieves a Merchant Center account. This method can only be called for
   * accounts to which the managing account has access: either the managing
   * account itself or sub-accounts if the managing account is a multi-client
   * account. (accounts.get)
   *
   * @param string $merchantId The ID of the managing account.
   * @param string $accountId The ID of the account.
   * @param array $optParams Optional parameters.
   * @return Google_Service_ShoppingContent_Account
   */
  public function get($merchantId, $accountId, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_ShoppingContent_Account");
  }
  /**
   * Creates a Merchant Center sub-account. This method can only be called for
   * multi-client accounts. (accounts.insert)
   *
   * @param string $merchantId The ID of the managing account.
   * @param Google_Service_ShoppingContent_Account $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool dryRun Flag to run the request in dry-run mode.
   * @return Google_Service_ShoppingContent_Account
   */
  public function insert($merchantId, Google_Service_ShoppingContent_Account $postBody, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_ShoppingContent_Account");
  }
  /**
   * Lists the sub-accounts in your Merchant Center account. This method can only
   * be called for multi-client accounts. (accounts.listAccounts)
   *
   * @param string $merchantId The ID of the managing account.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string maxResults The maximum number of accounts to return in the
   * response, used for paging.
   * @opt_param string pageToken The token returned by the previous request.
   * @return Google_Service_ShoppingContent_AccountsListResponse
   */
  public function listAccounts($merchantId, $optParams = array())
  {
    $params = array('merchantId' => $merchantId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_ShoppingContent_AccountsListResponse");
  }
  /**
   * Updates a Merchant Center account. This method can only be called for
   * accounts to which the managing account has access: either the managing
   * account itself or sub-accounts if the managing account is a multi-client
   * account. This method supports patch semantics. (accounts.patch)
   *
   * @param string $merchantId The ID of the managing account.
   * @param string $accountId The ID of the account.
   * @param Google_Service_ShoppingContent_Account $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool dryRun Flag to run the request in dry-run mode.
   * @return Google_Service_ShoppingContent_Account
   */
  public function patch($merchantId, $accountId, Google_Service_ShoppingContent_Account $postBody, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'accountId' => $accountId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_ShoppingContent_Account");
  }
  /**
   * Updates a Merchant Center account. This method can only be called for
   * accounts to which the managing account has access: either the managing
   * account itself or sub-accounts if the managing account is a multi-client
   * account. (accounts.update)
   *
   * @param string $merchantId The ID of the managing account.
   * @param string $accountId The ID of the account.
   * @param Google_Service_ShoppingContent_Account $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool dryRun Flag to run the request in dry-run mode.
   * @return Google_Service_ShoppingContent_Account
   */
  public function update($merchantId, $accountId, Google_Service_ShoppingContent_Account $postBody, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'accountId' => $accountId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_ShoppingContent_Account");
  }
}
