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

namespace Google\Service\DatabaseMigrationService\Resource;

use Google\Service\DatabaseMigrationService\ImportMappingRulesRequest;
use Google\Service\DatabaseMigrationService\Operation;

/**
 * The "mappingRules" collection of methods.
 * Typical usage is:
 *  <code>
 *   $datamigrationService = new Google\Service\DatabaseMigrationService(...);
 *   $mappingRules = $datamigrationService->projects_locations_conversionWorkspaces_mappingRules;
 *  </code>
 */
class ProjectsLocationsConversionWorkspacesMappingRules extends \Google\Service\Resource
{
  /**
   * Imports the mapping rules for a given conversion workspace. Supports various
   * formats of external rules files. (mappingRules.import)
   *
   * @param string $parent Required. Name of the conversion workspace resource to
   * import the rules to in the form of: projects/{project}/locations/{location}/c
   * onversionWorkspaces/{conversion_workspace}.
   * @param ImportMappingRulesRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Operation
   */
  public function import($parent, ImportMappingRulesRequest $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('import', [$params], Operation::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsConversionWorkspacesMappingRules::class, 'Google_Service_DatabaseMigrationService_Resource_ProjectsLocationsConversionWorkspacesMappingRules');
