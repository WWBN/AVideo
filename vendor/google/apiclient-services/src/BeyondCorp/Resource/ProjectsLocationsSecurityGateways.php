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

namespace Google\Service\BeyondCorp\Resource;

use Google\Service\BeyondCorp\GoogleCloudBeyondcorpSecuritygatewaysV1ListSecurityGatewaysResponse;
use Google\Service\BeyondCorp\GoogleCloudBeyondcorpSecuritygatewaysV1SecurityGateway;
use Google\Service\BeyondCorp\GoogleCloudBeyondcorpSecuritygatewaysV1SetPeeringRequest;
use Google\Service\BeyondCorp\GoogleLongrunningOperation;

/**
 * The "securityGateways" collection of methods.
 * Typical usage is:
 *  <code>
 *   $beyondcorpService = new Google\Service\BeyondCorp(...);
 *   $securityGateways = $beyondcorpService->projects_locations_securityGateways;
 *  </code>
 */
class ProjectsLocationsSecurityGateways extends \Google\Service\Resource
{
  /**
   * Creates a new SecurityGateway in a given project and location.
   * (securityGateways.create)
   *
   * @param string $parent Required. The resource project name of the
   * SecurityGateway location using the form:
   * `projects/{project_id}/locations/{location_id}`
   * @param GoogleCloudBeyondcorpSecuritygatewaysV1SecurityGateway $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string requestId Optional. An optional request ID to identify
   * requests. Specify a unique request ID so that if you must retry your request,
   * the server will know to ignore request if it has already been completed. The
   * server will guarantee that for at least 60 minutes since the first request.
   * @opt_param string securityGatewayId Optional. User-settable SecurityGateway
   * resource ID. * Must start with a letter. * Must contain between 4-63
   * characters from `/a-z-/`. * Must end with a number or letter.
   * @return GoogleLongrunningOperation
   * @throws \Google\Service\Exception
   */
  public function create($parent, GoogleCloudBeyondcorpSecuritygatewaysV1SecurityGateway $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], GoogleLongrunningOperation::class);
  }
  /**
   * Deletes a single SecurityGateway. (securityGateways.delete)
   *
   * @param string $name Required. BeyondCorp SecurityGateway name using the form:
   * `projects/{project_id}/locations/{location_id}/securityGateways/{security_gat
   * eway_id}`
   * @param array $optParams Optional parameters.
   *
   * @opt_param string requestId Optional. An optional request ID to identify
   * requests. Specify a unique request ID so that if you must retry your request,
   * the server will know to ignore the request if it has already been completed.
   * The server will guarantee that for at least 60 minutes after the first
   * request. For example, consider a situation where you make an initial request
   * and the request times out. If you make the request again with the same
   * request ID, the server can check if original operation with the same request
   * ID was received, and if so, will ignore the second request. This prevents
   * clients from accidentally creating duplicate commitments. The request ID must
   * be a valid UUID with the exception that zero UUID is not supported
   * (00000000-0000-0000-0000-000000000000).
   * @opt_param bool validateOnly Optional. If set, validates request by executing
   * a dry-run which would not alter the resource in any way.
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
   * Gets details of a single SecurityGateway. (securityGateways.get)
   *
   * @param string $name Required. The resource name of the PartnerTenant using
   * the form: `projects/{project_id}/locations/{location_id}/securityGateway/{sec
   * urity_gateway_id}`
   * @param array $optParams Optional parameters.
   * @return GoogleCloudBeyondcorpSecuritygatewaysV1SecurityGateway
   * @throws \Google\Service\Exception
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], GoogleCloudBeyondcorpSecuritygatewaysV1SecurityGateway::class);
  }
  /**
   * Lists SecurityGateways in a given project and location.
   * (securityGateways.listProjectsLocationsSecurityGateways)
   *
   * @param string $parent Required. The parent location to which the resources
   * belong. `projects/{project_id}/locations/{location_id}/`
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter Optional. A filter specifying constraints of a list
   * operation. All fields in the SecurityGateway message are supported. For
   * example, the following query will return the SecurityGateway with displayName
   * "test-security-gateway" For more information, please refer to
   * https://google.aip.dev/160.
   * @opt_param string orderBy Optional. Specifies the ordering of results. See
   * [Sorting
   * order](https://cloud.google.com/apis/design/design_patterns#sorting_order)
   * for more information.
   * @opt_param int pageSize Optional. The maximum number of items to return. If
   * not specified, a default value of 50 will be used by the service. Regardless
   * of the page_size value, the response may include a partial list and a caller
   * should only rely on response's next_page_token to determine if there are more
   * instances left to be queried.
   * @opt_param string pageToken Optional. The next_page_token value returned from
   * a previous ListSecurityGatewayRequest, if any.
   * @return GoogleCloudBeyondcorpSecuritygatewaysV1ListSecurityGatewaysResponse
   * @throws \Google\Service\Exception
   */
  public function listProjectsLocationsSecurityGateways($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], GoogleCloudBeyondcorpSecuritygatewaysV1ListSecurityGatewaysResponse::class);
  }
  /**
   * Updates the parameters of a single SecurityGateway. (securityGateways.patch)
   *
   * @param string $name Identifier. Name of the resource.
   * @param GoogleCloudBeyondcorpSecuritygatewaysV1SecurityGateway $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string requestId Optional. An optional request ID to identify
   * requests. Specify a unique request ID so that if you must retry your request,
   * the server will know to ignore the request if it has already been completed.
   * The server will guarantee that for at least 60 minutes after the first
   * request. For example, consider a situation where you make an initial request
   * and the request timed out. If you make the request again with the same
   * request ID, the server can check if original operation with the same request
   * ID was received, and if so, will ignore the second request. This prevents
   * clients from accidentally creating duplicate commitments. The request ID must
   * be a valid UUID with the exception that zero UUID is not supported
   * (00000000-0000-0000-0000-000000000000).
   * @opt_param string updateMask Required. Mutable fields include: display_name,
   * hubs.
   * @return GoogleLongrunningOperation
   * @throws \Google\Service\Exception
   */
  public function patch($name, GoogleCloudBeyondcorpSecuritygatewaysV1SecurityGateway $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], GoogleLongrunningOperation::class);
  }
  /**
   * This is a custom method to allow customers to create a peering connections
   * between Google network and customer networks. This is enabled only for the
   * allowlisted customers. (securityGateways.setPeering)
   *
   * @param string $securityGateway Required. BeyondCorp SecurityGateway name
   * using the form:
   * `projects/{project}/locations/{location}/securityGateways/{security_gateway}`
   * @param GoogleCloudBeyondcorpSecuritygatewaysV1SetPeeringRequest $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleLongrunningOperation
   * @throws \Google\Service\Exception
   */
  public function setPeering($securityGateway, GoogleCloudBeyondcorpSecuritygatewaysV1SetPeeringRequest $postBody, $optParams = [])
  {
    $params = ['securityGateway' => $securityGateway, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('setPeering', [$params], GoogleLongrunningOperation::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsSecurityGateways::class, 'Google_Service_BeyondCorp_Resource_ProjectsLocationsSecurityGateways');
