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

namespace Google\Service\SecurityCommandCenter\Resource;

use Google\Service\SecurityCommandCenter\GoogleCloudSecuritycenterV1SecurityHealthAnalyticsCustomModule;
use Google\Service\SecurityCommandCenter\ListDescendantSecurityHealthAnalyticsCustomModulesResponse;
use Google\Service\SecurityCommandCenter\ListSecurityHealthAnalyticsCustomModulesResponse;
use Google\Service\SecurityCommandCenter\SecuritycenterEmpty;

/**
 * The "customModules" collection of methods.
 * Typical usage is:
 *  <code>
 *   $securitycenterService = new Google\Service\SecurityCommandCenter(...);
 *   $customModules = $securitycenterService->folders_securityHealthAnalyticsSettings_customModules;
 *  </code>
 */
class FoldersSecurityHealthAnalyticsSettingsCustomModules extends \Google\Service\Resource
{
  /**
   * Creates a resident SecurityHealthAnalyticsCustomModule at the scope of the
   * given CRM parent, and also creates inherited
   * SecurityHealthAnalyticsCustomModules for all CRM descendants of the given
   * parent. These modules are enabled by default. (customModules.create)
   *
   * @param string $parent Required. Resource name of the new custom module's
   * parent. Its format is
   * "organizations/{organization}/securityHealthAnalyticsSettings",
   * "folders/{folder}/securityHealthAnalyticsSettings", or
   * "projects/{project}/securityHealthAnalyticsSettings"
   * @param GoogleCloudSecuritycenterV1SecurityHealthAnalyticsCustomModule $postBody
   * @param array $optParams Optional parameters.
   * @return GoogleCloudSecuritycenterV1SecurityHealthAnalyticsCustomModule
   */
  public function create($parent, GoogleCloudSecuritycenterV1SecurityHealthAnalyticsCustomModule $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], GoogleCloudSecuritycenterV1SecurityHealthAnalyticsCustomModule::class);
  }
  /**
   * Deletes the specified SecurityHealthAnalyticsCustomModule and all of its
   * descendants in the CRM hierarchy. This method is only supported for resident
   * custom modules. (customModules.delete)
   *
   * @param string $name Required. Name of the custom module to delete. Its format
   * is "organizations/{organization}/securityHealthAnalyticsSettings/customModule
   * s/{customModule}", "folders/{folder}/securityHealthAnalyticsSettings/customMo
   * dules/{customModule}", or "projects/{project}/securityHealthAnalyticsSettings
   * /customModules/{customModule}"
   * @param array $optParams Optional parameters.
   * @return SecuritycenterEmpty
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], SecuritycenterEmpty::class);
  }
  /**
   * Retrieves a SecurityHealthAnalyticsCustomModule. (customModules.get)
   *
   * @param string $name Required. Name of the custom module to get. Its format is
   * "organizations/{organization}/securityHealthAnalyticsSettings/customModules/{
   * customModule}", "folders/{folder}/securityHealthAnalyticsSettings/customModul
   * es/{customModule}", or "projects/{project}/securityHealthAnalyticsSettings/cu
   * stomModules/{customModule}"
   * @param array $optParams Optional parameters.
   * @return GoogleCloudSecuritycenterV1SecurityHealthAnalyticsCustomModule
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], GoogleCloudSecuritycenterV1SecurityHealthAnalyticsCustomModule::class);
  }
  /**
   * Returns a list of all SecurityHealthAnalyticsCustomModules for the given
   * parent. This includes resident modules defined at the scope of the parent,
   * and inherited modules, inherited from CRM ancestors.
   * (customModules.listFoldersSecurityHealthAnalyticsSettingsCustomModules)
   *
   * @param string $parent Required. Name of parent to list custom modules. Its
   * format is "organizations/{organization}/securityHealthAnalyticsSettings",
   * "folders/{folder}/securityHealthAnalyticsSettings", or
   * "projects/{project}/securityHealthAnalyticsSettings"
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize The maximum number of results to return in a single
   * response. Default is 10, minimum is 1, maximum is 1000.
   * @opt_param string pageToken The value returned by the last call indicating a
   * continuation
   * @return ListSecurityHealthAnalyticsCustomModulesResponse
   */
  public function listFoldersSecurityHealthAnalyticsSettingsCustomModules($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListSecurityHealthAnalyticsCustomModulesResponse::class);
  }
  /**
   * Returns a list of all resident SecurityHealthAnalyticsCustomModules under the
   * given CRM parent and all of the parent’s CRM descendants.
   * (customModules.listDescendant)
   *
   * @param string $parent Required. Name of parent to list descendant custom
   * modules. Its format is
   * "organizations/{organization}/securityHealthAnalyticsSettings",
   * "folders/{folder}/securityHealthAnalyticsSettings", or
   * "projects/{project}/securityHealthAnalyticsSettings"
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize The maximum number of results to return in a single
   * response. Default is 10, minimum is 1, maximum is 1000.
   * @opt_param string pageToken The value returned by the last call indicating a
   * continuation
   * @return ListDescendantSecurityHealthAnalyticsCustomModulesResponse
   */
  public function listDescendant($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('listDescendant', [$params], ListDescendantSecurityHealthAnalyticsCustomModulesResponse::class);
  }
  /**
   * Updates the SecurityHealthAnalyticsCustomModule under the given name based on
   * the given update mask. Updating the enablement state is supported on both
   * resident and inherited modules (though resident modules cannot have an
   * enablement state of "inherited"). Updating the display name and custom config
   * of a module is supported on resident modules only. (customModules.patch)
   *
   * @param string $name Immutable. The resource name of the custom module. Its
   * format is "organizations/{organization}/securityHealthAnalyticsSettings/custo
   * mModules/{customModule}", or "folders/{folder}/securityHealthAnalyticsSetting
   * s/customModules/{customModule}", or "projects/{project}/securityHealthAnalyti
   * csSettings/customModules/{customModule}" The id {customModule} is server-
   * generated and is not user settable. It will be a numeric id containing 1-20
   * digits.
   * @param GoogleCloudSecuritycenterV1SecurityHealthAnalyticsCustomModule $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask The list of fields to update.
   * @return GoogleCloudSecuritycenterV1SecurityHealthAnalyticsCustomModule
   */
  public function patch($name, GoogleCloudSecuritycenterV1SecurityHealthAnalyticsCustomModule $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], GoogleCloudSecuritycenterV1SecurityHealthAnalyticsCustomModule::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(FoldersSecurityHealthAnalyticsSettingsCustomModules::class, 'Google_Service_SecurityCommandCenter_Resource_FoldersSecurityHealthAnalyticsSettingsCustomModules');
