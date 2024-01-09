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

namespace Google\Service\ContainerAnalysis\Resource;

use Google\Service\ContainerAnalysis\GeneratePackagesSummaryRequest;
use Google\Service\ContainerAnalysis\PackagesSummaryResponse;

/**
 * The "resources" collection of methods.
 * Typical usage is:
 *  <code>
 *   $containeranalysisService = new Google\Service\ContainerAnalysis(...);
 *   $resources = $containeranalysisService->projects_resources;
 *  </code>
 */
class ProjectsResources extends \Google\Service\Resource
{
  /**
   * Gets a summary of the packages within a given resource.
   * (resources.generatePackagesSummary)
   *
   * @param string $name Required. The name of the resource to get a packages
   * summary for in the form of `projects/[PROJECT_ID]/resources/[RESOURCE_URL]`.
   * @param GeneratePackagesSummaryRequest $postBody
   * @param array $optParams Optional parameters.
   * @return PackagesSummaryResponse
   */
  public function generatePackagesSummary($name, GeneratePackagesSummaryRequest $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('generatePackagesSummary', [$params], PackagesSummaryResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsResources::class, 'Google_Service_ContainerAnalysis_Resource_ProjectsResources');
