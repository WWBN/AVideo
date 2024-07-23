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

namespace Google\Service\GKEHub\Resource;

use Google\Service\GKEHub\ListMembershipRBACRoleBindingsResponse;

/**
 * The "rbacrolebindings" collection of methods.
 * Typical usage is:
 *  <code>
 *   $gkehubService = new Google\Service\GKEHub(...);
 *   $rbacrolebindings = $gkehubService->projects_locations_memberships_rbacrolebindings;
 *  </code>
 */
class ProjectsLocationsMembershipsRbacrolebindings extends \Google\Service\Resource
{
  /**
   * Lists all Membership RBACRoleBindings.
   * (rbacrolebindings.listProjectsLocationsMembershipsRbacrolebindings)
   *
   * @param string $parent Required. The parent (project and location) where the
   * Features will be listed. Specified in the format
   * `projects/locations/memberships`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Optional. When requesting a 'page' of resources,
   * `page_size` specifies number of resources to return. If unspecified or set to
   * 0, all resources will be returned.
   * @opt_param string pageToken Optional. Token returned by previous call to
   * `ListMembershipRBACRoleBindings` which specifies the position in the list
   * from where to continue listing the resources.
   * @return ListMembershipRBACRoleBindingsResponse
   * @throws \Google\Service\Exception
   */
  public function listProjectsLocationsMembershipsRbacrolebindings($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListMembershipRBACRoleBindingsResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsMembershipsRbacrolebindings::class, 'Google_Service_GKEHub_Resource_ProjectsLocationsMembershipsRbacrolebindings');
