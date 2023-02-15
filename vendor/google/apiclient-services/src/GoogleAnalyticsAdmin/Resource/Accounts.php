<?php
/*
 * Copyright 2014 Google Inc.
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

namespace Google\Service\GoogleAnalyticsAdmin\Resource;

use Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaAccount;
use Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaDataSharingSettings;
use Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaListAccountsResponse;
use Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaProvisionAccountTicketRequest;
use Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaProvisionAccountTicketResponse;
use Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaSearchChangeHistoryEventsRequest;
use Google\Service\GoogleAnalyticsAdmin\GoogleAnalyticsAdminV1betaSearchChangeHistoryEventsResponse;
use Google\Service\GoogleAnalyticsAdmin\GoogleProtobufEmpty;

/**
 * The "accounts" collection of methods.
 * Typical usage is:
 *  <code>
 *   $analyticsadminService = new Google\Service\GoogleAnalyticsAdmin(...);
 *   $accounts = $analyticsadminService->accounts;
 *  </code>
 */
class Accounts extends \Google\Service\Resource
{
  /**
   * Marks target Account as soft-deleted (ie: "trashed") and returns it. This API
   * does not have a method to restore soft-deleted accounts. However, they can be
   * restored using the Trash Can UI. If the accounts are not restored before the
   * expiration time, the account and all child resources (eg: Properties,
   * GoogleAdsLinks, Streams, UserLinks) will be permanently purged.
   * https://support.google.com/analytics/answer/6154772 Returns an error if the
   * target is not found. (accounts.delete)
   *
   * @param string $name Required. The name of the Account to soft-delete. Format:
   * accounts/{account} Example: "accounts/100"
   * @param array $optParams Optional parameters.
   * @return GoogleProtobufEmpty
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], GoogleProtobufEmpty::class);
  }
  /**
   * Lookup for a single Account. (accounts.get)
   *
   * @param string $name Required. The name of the account to lookup. Format:
   * accounts/{account} Example: "accounts/100"
   * @param array $optParams Optional parameters.
   * @return GoogleAnalyticsAdminV1betaAccount
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], GoogleAnalyticsAdminV1betaAccount::class);
  }
  /**
   * Get data sharing settings on an account. Data sharing settings are
   * singletons. (accounts.getDataSharingSettings)
   *
   * @param string $name Required. The name of the settings to lookup. Format:
   * accounts/{account}/dataSharingSettings Example:
   * "accounts/1000/dataSharingSettings"
   * @param array $optParams Optional parameters.
   * @return GoogleAnalyticsAdminV1betaDataSharingSettings
   */
  public function getDataSharingSettings($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('getDataSharingSettings', [$params], GoogleAnalyticsAdminV1betaDataSharingSettings::class);
  }
  /**
   * Returns all accounts accessible by the caller. Note that these accounts might
   * not currently have GA4 properties. Soft-deleted (ie: "trashed") accounts are
   * excluded by default. Returns an empty list if no relevant accounts are found.
   * (accounts.listAccounts)
   *
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize The maximum number of resources to return. The
   * service may return fewer than this value, even if there are additional pages.
   * If unspecified, at most 50 resources will be returned. The maximum value is
   * 200; (higher values will be coerced to the maximum)
   * @opt_param string pageToken A page token, received from a previous
   * `ListAccounts` call. Provide this to retrieve the subsequent page. When
   * paginating, all other parameters provided to `ListAccounts` must match the
   * call that provided the page token.
   * @opt_param bool showDeleted Whether to include soft-deleted (ie: "trashed")
   * Accounts in the results. Accounts can be inspected to determine whether they
   * are deleted or not.
   * @return GoogleAnalyticsAdminV1betaListAccountsResponse
   */
  public function listAccounts($optParams = [])
  {
    $params = [];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], GoogleAnalyticsAdminV1betaListAccountsResponse::class);
  }
  /**
   * Updates an account. (accounts.patch)
   *
   * @param string $name Output only. Resource name of this account. Format:
   * accounts/{account} Example: "accounts/100"
   * @param GoogleAnalyticsAdminV1betaAccount $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask Required. The list of fields to be updated.
   * Field names must be in snake case (e.g., "field_to_update"). Omitted fields
   * will not be updated. To replace the entire entity, use one path with the
   * string "*" to match all fields.
   * @return GoogleAnalyticsAdminV1betaAccount
   */
  public function patch($name, GoogleAnalyticsAdminV1betaAccount $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], GoogleAnalyticsAdminV1betaAccount::class);
  }
  /**
   * Requests a ticket for creating an account. (accounts.provisionAccountTicket)
   *
   * @param GoogleAnalyticsAdminV1betaProvisionAccountTicketRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleAnalyticsAdminV1betaProvisionAccountTicketResponse
   */
  public function provisionAccountTicket(GoogleAnalyticsAdminV1betaProvisionAccountTicketRequest $postBody, $optParams = [])
  {
    $params = ['postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('provisionAccountTicket', [$params], GoogleAnalyticsAdminV1betaProvisionAccountTicketResponse::class);
  }
  /**
   * Searches through all changes to an account or its children given the
   * specified set of filters. (accounts.searchChangeHistoryEvents)
   *
   * @param string $account Required. The account resource for which to return
   * change history resources.
   * @param GoogleAnalyticsAdminV1betaSearchChangeHistoryEventsRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleAnalyticsAdminV1betaSearchChangeHistoryEventsResponse
   */
  public function searchChangeHistoryEvents($account, GoogleAnalyticsAdminV1betaSearchChangeHistoryEventsRequest $postBody, $optParams = [])
  {
    $params = ['account' => $account, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('searchChangeHistoryEvents', [$params], GoogleAnalyticsAdminV1betaSearchChangeHistoryEventsResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Accounts::class, 'Google_Service_GoogleAnalyticsAdmin_Resource_Accounts');
