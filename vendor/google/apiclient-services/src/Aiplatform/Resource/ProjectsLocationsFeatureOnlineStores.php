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

namespace Google\Service\Aiplatform\Resource;

use Google\Service\Aiplatform\GoogleCloudAiplatformV1FeatureOnlineStore;
use Google\Service\Aiplatform\GoogleCloudAiplatformV1ListFeatureOnlineStoresResponse;
use Google\Service\Aiplatform\GoogleLongrunningOperation;

/**
 * The "featureOnlineStores" collection of methods.
 * Typical usage is:
 *  <code>
 *   $aiplatformService = new Google\Service\Aiplatform(...);
 *   $featureOnlineStores = $aiplatformService->projects_locations_featureOnlineStores;
 *  </code>
 */
class ProjectsLocationsFeatureOnlineStores extends \Google\Service\Resource
{
  /**
   * Creates a new FeatureOnlineStore in a given project and location.
   * (featureOnlineStores.create)
   *
   * @param string $parent Required. The resource name of the Location to create
   * FeatureOnlineStores. Format: `projects/{project}/locations/{location}`
   * @param GoogleCloudAiplatformV1FeatureOnlineStore $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string featureOnlineStoreId Required. The ID to use for this
   * FeatureOnlineStore, which will become the final component of the
   * FeatureOnlineStore's resource name. This value may be up to 60 characters,
   * and valid characters are `[a-z0-9_]`. The first character cannot be a number.
   * The value must be unique within the project and location.
   * @return GoogleLongrunningOperation
   * @throws \Google\Service\Exception
   */
  public function create($parent, GoogleCloudAiplatformV1FeatureOnlineStore $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], GoogleLongrunningOperation::class);
  }
  /**
   * Deletes a single FeatureOnlineStore. The FeatureOnlineStore must not contain
   * any FeatureViews. (featureOnlineStores.delete)
   *
   * @param string $name Required. The name of the FeatureOnlineStore to be
   * deleted. Format: `projects/{project}/locations/{location}/featureOnlineStores
   * /{feature_online_store}`
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool force If set to true, any FeatureViews and Features for this
   * FeatureOnlineStore will also be deleted. (Otherwise, the request will only
   * work if the FeatureOnlineStore has no FeatureViews.)
   * @return GoogleLongrunningOperation
   * @throws \Google\Service\Exception
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], GoogleLongrunningOperation::class);
  }
  /**
   * Gets details of a single FeatureOnlineStore. (featureOnlineStores.get)
   *
   * @param string $name Required. The name of the FeatureOnlineStore resource.
   * @param array $optParams Optional parameters.
   * @return GoogleCloudAiplatformV1FeatureOnlineStore
   * @throws \Google\Service\Exception
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], GoogleCloudAiplatformV1FeatureOnlineStore::class);
  }
  /**
   * Lists FeatureOnlineStores in a given project and location.
   * (featureOnlineStores.listProjectsLocationsFeatureOnlineStores)
   *
   * @param string $parent Required. The resource name of the Location to list
   * FeatureOnlineStores. Format: `projects/{project}/locations/{location}`
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter Lists the FeatureOnlineStores that match the filter
   * expression. The following fields are supported: * `create_time`: Supports
   * `=`, `!=`, `<`, `>`, `<=`, and `>=` comparisons. Values must be in RFC 3339
   * format. * `update_time`: Supports `=`, `!=`, `<`, `>`, `<=`, and `>=`
   * comparisons. Values must be in RFC 3339 format. * `labels`: Supports key-
   * value equality and key presence. Examples: * `create_time > "2020-01-01" OR
   * update_time > "2020-01-01"` FeatureOnlineStores created or updated after
   * 2020-01-01. * `labels.env = "prod"` FeatureOnlineStores with label "env" set
   * to "prod".
   * @opt_param string orderBy A comma-separated list of fields to order by,
   * sorted in ascending order. Use "desc" after a field name for descending.
   * Supported Fields: * `create_time` * `update_time`
   * @opt_param int pageSize The maximum number of FeatureOnlineStores to return.
   * The service may return fewer than this value. If unspecified, at most 100
   * FeatureOnlineStores will be returned. The maximum value is 100; any value
   * greater than 100 will be coerced to 100.
   * @opt_param string pageToken A page token, received from a previous
   * FeatureOnlineStoreAdminService.ListFeatureOnlineStores call. Provide this to
   * retrieve the subsequent page. When paginating, all other parameters provided
   * to FeatureOnlineStoreAdminService.ListFeatureOnlineStores must match the call
   * that provided the page token.
   * @return GoogleCloudAiplatformV1ListFeatureOnlineStoresResponse
   * @throws \Google\Service\Exception
   */
  public function listProjectsLocationsFeatureOnlineStores($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], GoogleCloudAiplatformV1ListFeatureOnlineStoresResponse::class);
  }
  /**
   * Updates the parameters of a single FeatureOnlineStore.
   * (featureOnlineStores.patch)
   *
   * @param string $name Identifier. Name of the FeatureOnlineStore. Format: `proj
   * ects/{project}/locations/{location}/featureOnlineStores/{featureOnlineStore}`
   * @param GoogleCloudAiplatformV1FeatureOnlineStore $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask Field mask is used to specify the fields to be
   * overwritten in the FeatureOnlineStore resource by the update. The fields
   * specified in the update_mask are relative to the resource, not the full
   * request. A field will be overwritten if it is in the mask. If the user does
   * not provide a mask then only the non-empty fields present in the request will
   * be overwritten. Set the update_mask to `*` to override all fields. Updatable
   * fields: * `big_query_source` * `bigtable` * `labels` * `sync_config`
   * @return GoogleLongrunningOperation
   * @throws \Google\Service\Exception
   */
  public function patch($name, GoogleCloudAiplatformV1FeatureOnlineStore $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], GoogleLongrunningOperation::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsFeatureOnlineStores::class, 'Google_Service_Aiplatform_Resource_ProjectsLocationsFeatureOnlineStores');
