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

namespace Google\Service\NetworkServices\Resource;

use Google\Service\NetworkServices\ListServiceLbPoliciesResponse;
use Google\Service\NetworkServices\Operation;
use Google\Service\NetworkServices\Policy;
use Google\Service\NetworkServices\ServiceLbPolicy;
use Google\Service\NetworkServices\SetIamPolicyRequest;
use Google\Service\NetworkServices\TestIamPermissionsRequest;
use Google\Service\NetworkServices\TestIamPermissionsResponse;

/**
 * The "serviceLbPolicies" collection of methods.
 * Typical usage is:
 *  <code>
 *   $networkservicesService = new Google\Service\NetworkServices(...);
 *   $serviceLbPolicies = $networkservicesService->projects_locations_serviceLbPolicies;
 *  </code>
 */
class ProjectsLocationsServiceLbPolicies extends \Google\Service\Resource
{
  /**
   * Creates a new ServiceLbPolicy in a given project and location.
   * (serviceLbPolicies.create)
   *
   * @param string $parent Required. The parent resource of the ServiceLbPolicy.
   * Must be in the format `projects/{project}/locations/{location}`.
   * @param ServiceLbPolicy $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string serviceLbPolicyId Required. Short name of the
   * ServiceLbPolicy resource to be created. E.g. for resource name `projects/{pro
   * ject}/locations/{location}/serviceLbPolicies/{service_lb_policy_name}`. the
   * id is value of {service_lb_policy_name}
   * @return Operation
   * @throws \Google\Service\Exception
   */
  public function create($parent, ServiceLbPolicy $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], Operation::class);
  }
  /**
   * Deletes a single ServiceLbPolicy. (serviceLbPolicies.delete)
   *
   * @param string $name Required. A name of the ServiceLbPolicy to delete. Must
   * be in the format `projects/{project}/locations/{location}/serviceLbPolicies`.
   * @param array $optParams Optional parameters.
   * @return Operation
   * @throws \Google\Service\Exception
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], Operation::class);
  }
  /**
   * Gets details of a single ServiceLbPolicy. (serviceLbPolicies.get)
   *
   * @param string $name Required. A name of the ServiceLbPolicy to get. Must be
   * in the format `projects/{project}/locations/{location}/serviceLbPolicies`.
   * @param array $optParams Optional parameters.
   * @return ServiceLbPolicy
   * @throws \Google\Service\Exception
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], ServiceLbPolicy::class);
  }
  /**
   * Gets the access control policy for a resource. Returns an empty policy if the
   * resource exists and does not have a policy set.
   * (serviceLbPolicies.getIamPolicy)
   *
   * @param string $resource REQUIRED: The resource for which the policy is being
   * requested. See [Resource
   * names](https://cloud.google.com/apis/design/resource_names) for the
   * appropriate value for this field.
   * @param array $optParams Optional parameters.
   *
   * @opt_param int options.requestedPolicyVersion Optional. The maximum policy
   * version that will be used to format the policy. Valid values are 0, 1, and 3.
   * Requests specifying an invalid value will be rejected. Requests for policies
   * with any conditional role bindings must specify version 3. Policies with no
   * conditional role bindings may specify any valid value or leave the field
   * unset. The policy in the response might use the policy version that you
   * specified, or it might use a lower policy version. For example, if you
   * specify version 3, but the policy has no conditional role bindings, the
   * response uses version 1. To learn which resources support conditions in their
   * IAM policies, see the [IAM
   * documentation](https://cloud.google.com/iam/help/conditions/resource-
   * policies).
   * @return Policy
   * @throws \Google\Service\Exception
   */
  public function getIamPolicy($resource, $optParams = [])
  {
    $params = ['resource' => $resource];
    $params = array_merge($params, $optParams);
    return $this->call('getIamPolicy', [$params], Policy::class);
  }
  /**
   * Lists ServiceLbPolicies in a given project and location.
   * (serviceLbPolicies.listProjectsLocationsServiceLbPolicies)
   *
   * @param string $parent Required. The project and location from which the
   * ServiceLbPolicies should be listed, specified in the format
   * `projects/{project}/locations/{location}`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Maximum number of ServiceLbPolicies to return per
   * call.
   * @opt_param string pageToken The value returned by the last
   * `ListServiceLbPoliciesResponse` Indicates that this is a continuation of a
   * prior `ListRouters` call, and that the system should return the next page of
   * data.
   * @return ListServiceLbPoliciesResponse
   * @throws \Google\Service\Exception
   */
  public function listProjectsLocationsServiceLbPolicies($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListServiceLbPoliciesResponse::class);
  }
  /**
   * Updates the parameters of a single ServiceLbPolicy. (serviceLbPolicies.patch)
   *
   * @param string $name Required. Name of the ServiceLbPolicy resource. It
   * matches pattern `projects/{project}/locations/{location}/serviceLbPolicies/{s
   * ervice_lb_policy_name}`.
   * @param ServiceLbPolicy $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask Optional. Field mask is used to specify the
   * fields to be overwritten in the ServiceLbPolicy resource by the update. The
   * fields specified in the update_mask are relative to the resource, not the
   * full request. A field will be overwritten if it is in the mask. If the user
   * does not provide a mask then all fields will be overwritten.
   * @return Operation
   * @throws \Google\Service\Exception
   */
  public function patch($name, ServiceLbPolicy $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], Operation::class);
  }
  /**
   * Sets the access control policy on the specified resource. Replaces any
   * existing policy. Can return `NOT_FOUND`, `INVALID_ARGUMENT`, and
   * `PERMISSION_DENIED` errors. (serviceLbPolicies.setIamPolicy)
   *
   * @param string $resource REQUIRED: The resource for which the policy is being
   * specified. See [Resource
   * names](https://cloud.google.com/apis/design/resource_names) for the
   * appropriate value for this field.
   * @param SetIamPolicyRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Policy
   * @throws \Google\Service\Exception
   */
  public function setIamPolicy($resource, SetIamPolicyRequest $postBody, $optParams = [])
  {
    $params = ['resource' => $resource, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('setIamPolicy', [$params], Policy::class);
  }
  /**
   * Returns permissions that a caller has on the specified resource. If the
   * resource does not exist, this will return an empty set of permissions, not a
   * `NOT_FOUND` error. Note: This operation is designed to be used for building
   * permission-aware UIs and command-line tools, not for authorization checking.
   * This operation may "fail open" without warning.
   * (serviceLbPolicies.testIamPermissions)
   *
   * @param string $resource REQUIRED: The resource for which the policy detail is
   * being requested. See [Resource
   * names](https://cloud.google.com/apis/design/resource_names) for the
   * appropriate value for this field.
   * @param TestIamPermissionsRequest $postBody
   * @param array $optParams Optional parameters.
   * @return TestIamPermissionsResponse
   * @throws \Google\Service\Exception
   */
  public function testIamPermissions($resource, TestIamPermissionsRequest $postBody, $optParams = [])
  {
    $params = ['resource' => $resource, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('testIamPermissions', [$params], TestIamPermissionsResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsServiceLbPolicies::class, 'Google_Service_NetworkServices_Resource_ProjectsLocationsServiceLbPolicies');
