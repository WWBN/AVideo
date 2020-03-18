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
 * The "accountshipping" collection of methods.
 * Typical usage is:
 *  <code>
 *   $contentService = new Google_Service_ShoppingContent(...);
 *   $accountshipping = $contentService->accountshipping;
 *  </code>
 */
class Google_Service_ShoppingContent_Resource_Accountshipping extends Google_Service_Resource
{
  /**
   * Retrieves and updates the shipping settings of multiple accounts in a single
   * request. (accountshipping.custombatch)
   *
   * @param Google_Service_ShoppingContent_AccountshippingCustomBatchRequest $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool dryRun Flag to run the request in dry-run mode.
   * @return Google_Service_ShoppingContent_AccountshippingCustomBatchResponse
   */
  public function custombatch(Google_Service_ShoppingContent_AccountshippingCustomBatchRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('custombatch', array($params), "Google_Service_ShoppingContent_AccountshippingCustomBatchResponse");
  }
  /**
   * Retrieves the shipping settings of the account. This method can only be
   * called for accounts to which the managing account has access: either the
   * managing account itself or sub-accounts if the managing account is a multi-
   * client account. (accountshipping.get)
   *
   * @param string $merchantId The ID of the managing account.
   * @param string $accountId The ID of the account for which to get/update
   * account shipping settings.
   * @param array $optParams Optional parameters.
   * @return Google_Service_ShoppingContent_AccountShipping
   */
  public function get($merchantId, $accountId, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_ShoppingContent_AccountShipping");
  }
  /**
   * Lists the shipping settings of the sub-accounts in your Merchant Center
   * account. This method can only be called for multi-client accounts.
   * (accountshipping.listAccountshipping)
   *
   * @param string $merchantId The ID of the managing account.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string maxResults The maximum number of shipping settings to
   * return in the response, used for paging.
   * @opt_param string pageToken The token returned by the previous request.
   * @return Google_Service_ShoppingContent_AccountshippingListResponse
   */
  public function listAccountshipping($merchantId, $optParams = array())
  {
    $params = array('merchantId' => $merchantId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_ShoppingContent_AccountshippingListResponse");
  }
  /**
   * Updates the shipping settings of the account. This method can only be called
   * for accounts to which the managing account has access: either the managing
   * account itself or sub-accounts if the managing account is a multi-client
   * account. This method supports patch semantics. (accountshipping.patch)
   *
   * @param string $merchantId The ID of the managing account.
   * @param string $accountId The ID of the account for which to get/update
   * account shipping settings.
   * @param Google_Service_ShoppingContent_AccountShipping $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool dryRun Flag to run the request in dry-run mode.
   * @return Google_Service_ShoppingContent_AccountShipping
   */
  public function patch($merchantId, $accountId, Google_Service_ShoppingContent_AccountShipping $postBody, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'accountId' => $accountId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_ShoppingContent_AccountShipping");
  }
  /**
   * Updates the shipping settings of the account. This method can only be called
   * for accounts to which the managing account has access: either the managing
   * account itself or sub-accounts if the managing account is a multi-client
   * account. (accountshipping.update)
   *
   * @param string $merchantId The ID of the managing account.
   * @param string $accountId The ID of the account for which to get/update
   * account shipping settings.
   * @param Google_Service_ShoppingContent_AccountShipping $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool dryRun Flag to run the request in dry-run mode.
   * @return Google_Service_ShoppingContent_AccountShipping
   */
  public function update($merchantId, $accountId, Google_Service_ShoppingContent_AccountShipping $postBody, $optParams = array())
  {
    $params = array('merchantId' => $merchantId, 'accountId' => $accountId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_ShoppingContent_AccountShipping");
  }
}
