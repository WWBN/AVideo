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

namespace Google\Service\Appengine\Resource;

use Google\Service\Appengine\DomainMapping;

/**
 * The "domainMappings" collection of methods.
 * Typical usage is:
 *  <code>
 *   $appengineService = new Google\Service\Appengine(...);
 *   $domainMappings = $appengineService->projects_locations_applications_domainMappings;
 *  </code>
 */
class ProjectsLocationsApplicationsDomainMappings extends \Google\Service\Resource
{
  /**
   * Gets the specified domain mapping. (domainMappings.get)
   *
   * @param string $projectsId Part of `name`. Name of the resource requested.
   * Example: apps/myapp/domainMappings/example.com.
   * @param string $locationsId Part of `name`. See documentation of `projectsId`.
   * @param string $applicationsId Part of `name`. See documentation of
   * `projectsId`.
   * @param string $domainMappingsId Part of `name`. See documentation of
   * `projectsId`.
   * @param array $optParams Optional parameters.
   * @return DomainMapping
   * @throws \Google\Service\Exception
   */
  public function get($projectsId, $locationsId, $applicationsId, $domainMappingsId, $optParams = [])
  {
    $params = ['projectsId' => $projectsId, 'locationsId' => $locationsId, 'applicationsId' => $applicationsId, 'domainMappingsId' => $domainMappingsId];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], DomainMapping::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsApplicationsDomainMappings::class, 'Google_Service_Appengine_Resource_ProjectsLocationsApplicationsDomainMappings');
