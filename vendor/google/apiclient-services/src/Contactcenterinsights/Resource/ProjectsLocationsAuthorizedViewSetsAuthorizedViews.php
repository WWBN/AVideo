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

namespace Google\Service\Contactcenterinsights\Resource;

use Google\Service\Contactcenterinsights\GoogleCloudContactcenterinsightsV1AuthorizedView;
use Google\Service\Contactcenterinsights\GoogleCloudContactcenterinsightsV1ListAuthorizedViewsResponse;
use Google\Service\Contactcenterinsights\GoogleCloudContactcenterinsightsV1QueryMetricsRequest;
use Google\Service\Contactcenterinsights\GoogleCloudContactcenterinsightsV1SearchAuthorizedViewsResponse;
use Google\Service\Contactcenterinsights\GoogleLongrunningOperation;
use Google\Service\Contactcenterinsights\GoogleProtobufEmpty;

/**
 * The "authorizedViews" collection of methods.
 * Typical usage is:
 *  <code>
 *   $contactcenterinsightsService = new Google\Service\Contactcenterinsights(...);
 *   $authorizedViews = $contactcenterinsightsService->projects_locations_authorizedViewSets_authorizedViews;
 *  </code>
 */
class ProjectsLocationsAuthorizedViewSetsAuthorizedViews extends \Google\Service\Resource
{
  /**
   * Create AuthorizedView (authorizedViews.create)
   *
   * @param string $parent Required. The parent resource of the AuthorizedView.
   * @param GoogleCloudContactcenterinsightsV1AuthorizedView $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string authorizedViewId Optional. A unique ID for the new
   * AuthorizedView. This ID will become the final component of the
   * AuthorizedView's resource name. If no ID is specified, a server-generated ID
   * will be used. This value should be 4-64 characters and must match the regular
   * expression `^[a-z]([a-z0-9-]{0,61}[a-z0-9])?$`. See go/aip/122#resource-id-
   * segments
   * @return GoogleCloudContactcenterinsightsV1AuthorizedView
   * @throws \Google\Service\Exception
   */
  public function create($parent, GoogleCloudContactcenterinsightsV1AuthorizedView $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], GoogleCloudContactcenterinsightsV1AuthorizedView::class);
  }
  /**
   * Deletes an AuthorizedView. (authorizedViews.delete)
   *
   * @param string $name Required. The name of the AuthorizedView to delete.
   * @param array $optParams Optional parameters.
   * @return GoogleProtobufEmpty
   * @throws \Google\Service\Exception
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], GoogleProtobufEmpty::class);
  }
  /**
   * Get AuthorizedView (authorizedViews.get)
   *
   * @param string $name Required. The name of the AuthorizedView to get.
   * @param array $optParams Optional parameters.
   * @return GoogleCloudContactcenterinsightsV1AuthorizedView
   * @throws \Google\Service\Exception
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], GoogleCloudContactcenterinsightsV1AuthorizedView::class);
  }
  /**
   * List AuthorizedViewSets
   * (authorizedViews.listProjectsLocationsAuthorizedViewSetsAuthorizedViews)
   *
   * @param string $parent Required. The parent resource of the AuthorizedViews.
   * If the parent is set to `-`, all AuthorizedViews under the location will be
   * returned.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter Optional. The filter expression to filter authorized
   * views listed in the response.
   * @opt_param string orderBy Optional. The order by expression to order
   * authorized views listed in the response.
   * @opt_param int pageSize Optional. The maximum number of view to return in the
   * response. If the value is zero, the service will select a default size. A
   * call might return fewer objects than requested. A non-empty `next_page_token`
   * in the response indicates that more data is available.
   * @opt_param string pageToken Optional. The value returned by the last
   * `ListAuthorizedViewsResponse`. This value indicates that this is a
   * continuation of a prior `ListAuthorizedViews` call and that the system should
   * return the next page of data.
   * @return GoogleCloudContactcenterinsightsV1ListAuthorizedViewsResponse
   * @throws \Google\Service\Exception
   */
  public function listProjectsLocationsAuthorizedViewSetsAuthorizedViews($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], GoogleCloudContactcenterinsightsV1ListAuthorizedViewsResponse::class);
  }
  /**
   * Updates an AuthorizedView. (authorizedViews.patch)
   *
   * @param string $name Identifier. The resource name of the AuthorizedView.
   * Format: projects/{project}/locations/{location}/authorizedViewSets/{authorize
   * d_view_set}/authorizedViews/{authorized_view}
   * @param GoogleCloudContactcenterinsightsV1AuthorizedView $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask Optional. The list of fields to be updated. All
   * possible fields can be updated by passing `*`, or a subset of the following
   * updateable fields can be provided: * `conversation_filter` * `display_name`
   * @return GoogleCloudContactcenterinsightsV1AuthorizedView
   * @throws \Google\Service\Exception
   */
  public function patch($name, GoogleCloudContactcenterinsightsV1AuthorizedView $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], GoogleCloudContactcenterinsightsV1AuthorizedView::class);
  }
  /**
   * Query metrics. (authorizedViews.queryMetrics)
   *
   * @param string $location Required. The location of the data.
   * "projects/{project}/locations/{location}"
   * @param GoogleCloudContactcenterinsightsV1QueryMetricsRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleLongrunningOperation
   * @throws \Google\Service\Exception
   */
  public function queryMetrics($location, GoogleCloudContactcenterinsightsV1QueryMetricsRequest $postBody, $optParams = [])
  {
    $params = ['location' => $location, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('queryMetrics', [$params], GoogleLongrunningOperation::class);
  }
  /**
   * SearchAuthorizedViewSets (authorizedViews.search)
   *
   * @param string $parent Required. The parent resource of the AuthorizedViews.
   * If the parent is set to `-`, all AuthorizedViews under the location will be
   * returned.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string orderBy Optional. The order by expression to order
   * authorized views listed in the response.
   * @opt_param int pageSize Optional. The maximum number of view to return in the
   * response. If the value is zero, the service will select a default size. A
   * call might return fewer objects than requested. A non-empty `next_page_token`
   * in the response indicates that more data is available.
   * @opt_param string pageToken Optional. The value returned by the last
   * `ListAuthorizedViewsResponse`. This value indicates that this is a
   * continuation of a prior `ListAuthorizedViews` call and that the system should
   * return the next page of data.
   * @opt_param string query Optional. The query expression to search authorized
   * views.
   * @return GoogleCloudContactcenterinsightsV1SearchAuthorizedViewsResponse
   * @throws \Google\Service\Exception
   */
  public function search($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('search', [$params], GoogleCloudContactcenterinsightsV1SearchAuthorizedViewsResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsAuthorizedViewSetsAuthorizedViews::class, 'Google_Service_Contactcenterinsights_Resource_ProjectsLocationsAuthorizedViewSetsAuthorizedViews');
