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

namespace Google\Service\Logging\Resource;

use Google\Service\Logging\ListSavedQueriesResponse;
use Google\Service\Logging\LoggingEmpty;
use Google\Service\Logging\SavedQuery;

/**
 * The "savedQueries" collection of methods.
 * Typical usage is:
 *  <code>
 *   $loggingService = new Google\Service\Logging(...);
 *   $savedQueries = $loggingService->billingAccounts_locations_savedQueries;
 *  </code>
 */
class BillingAccountsLocationsSavedQueries extends \Google\Service\Resource
{
  /**
   * Creates a new SavedQuery for the user making the request.
   * (savedQueries.create)
   *
   * @param string $parent Required. The parent resource in which to create the
   * saved query: "projects/[PROJECT_ID]/locations/[LOCATION_ID]"
   * "organizations/[ORGANIZATION_ID]/locations/[LOCATION_ID]"
   * "billingAccounts/[BILLING_ACCOUNT_ID]/locations/[LOCATION_ID]"
   * "folders/[FOLDER_ID]/locations/[LOCATION_ID]" For example: "projects/my-
   * project/locations/global" "organizations/123456789/locations/us-central1"
   * @param SavedQuery $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string savedQueryId Optional. The ID to use for the saved query,
   * which will become the final component of the saved query's resource name.If
   * the saved_query_id is not provided, the system will generate an alphanumeric
   * ID.The saved_query_id is limited to 100 characters and can include only the
   * following characters: upper and lower-case alphanumeric characters,
   * underscores, hyphens, periods.First character has to be alphanumeric.
   * @return SavedQuery
   * @throws \Google\Service\Exception
   */
  public function create($parent, SavedQuery $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], SavedQuery::class);
  }
  /**
   * Deletes an existing SavedQuery that was created by the user making the
   * request. (savedQueries.delete)
   *
   * @param string $name Required. The full resource name of the saved query to
   * delete.
   * "projects/[PROJECT_ID]/locations/[LOCATION_ID]/savedQueries/[QUERY_ID]" "orga
   * nizations/[ORGANIZATION_ID]/locations/[LOCATION_ID]/savedQueries/[QUERY_ID]"
   * "billingAccounts/[BILLING_ACCOUNT_ID]/locations/[LOCATION_ID]/savedQueries/[Q
   * UERY_ID]"
   * "folders/[FOLDER_ID]/locations/[LOCATION_ID]/savedQueries/[QUERY_ID]" For
   * example: "projects/my-project/locations/global/savedQueries/my-saved-query"
   * @param array $optParams Optional parameters.
   * @return LoggingEmpty
   * @throws \Google\Service\Exception
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], LoggingEmpty::class);
  }
  /**
   * Lists the SavedQueries that were created by the user making the request.
   * (savedQueries.listBillingAccountsLocationsSavedQueries)
   *
   * @param string $parent Required. The resource to which the listed queries
   * belong. "projects/[PROJECT_ID]/locations/[LOCATION_ID]"
   * "organizations/[ORGANIZATION_ID]/locations/[LOCATION_ID]"
   * "billingAccounts/[BILLING_ACCOUNT_ID]/locations/[LOCATION_ID]"
   * "folders/[FOLDER_ID]/locations/[LOCATION_ID]" For example: "projects/my-
   * project/locations/us-central1" Note: The locations portion of the resource
   * must be specified. To get a list of all saved queries, a wildcard character -
   * can be used for LOCATION_ID, for example: "projects/my-project/locations/-"
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Optional. The maximum number of results to return
   * from this request.Non-positive values are ignored. The presence of
   * nextPageToken in the response indicates that more results might be available.
   * @opt_param string pageToken Optional. If present, then retrieve the next
   * batch of results from the preceding call to this method. pageToken must be
   * the value of nextPageToken from the previous response. The values of other
   * method parameters should be identical to those in the previous call.
   * @return ListSavedQueriesResponse
   * @throws \Google\Service\Exception
   */
  public function listBillingAccountsLocationsSavedQueries($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListSavedQueriesResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(BillingAccountsLocationsSavedQueries::class, 'Google_Service_Logging_Resource_BillingAccountsLocationsSavedQueries');
