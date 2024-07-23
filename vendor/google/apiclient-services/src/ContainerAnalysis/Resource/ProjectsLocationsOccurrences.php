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

use Google\Service\ContainerAnalysis\ListOccurrencesResponse;
use Google\Service\ContainerAnalysis\Note;
use Google\Service\ContainerAnalysis\Occurrence;
use Google\Service\ContainerAnalysis\VulnerabilityOccurrencesSummary;

/**
 * The "occurrences" collection of methods.
 * Typical usage is:
 *  <code>
 *   $containeranalysisService = new Google\Service\ContainerAnalysis(...);
 *   $occurrences = $containeranalysisService->projects_locations_occurrences;
 *  </code>
 */
class ProjectsLocationsOccurrences extends \Google\Service\Resource
{
  /**
   * Gets the specified occurrence. (occurrences.get)
   *
   * @param string $name Required. The name of the occurrence in the form of
   * `projects/[PROJECT_ID]/occurrences/[OCCURRENCE_ID]`.
   * @param array $optParams Optional parameters.
   * @return Occurrence
   * @throws \Google\Service\Exception
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], Occurrence::class);
  }
  /**
   * Gets the note attached to the specified occurrence. Consumer projects can use
   * this method to get a note that belongs to a provider project.
   * (occurrences.getNotes)
   *
   * @param string $name Required. The name of the occurrence in the form of
   * `projects/[PROJECT_ID]/occurrences/[OCCURRENCE_ID]`.
   * @param array $optParams Optional parameters.
   * @return Note
   * @throws \Google\Service\Exception
   */
  public function getNotes($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('getNotes', [$params], Note::class);
  }
  /**
   * Gets a summary of the number and severity of occurrences.
   * (occurrences.getVulnerabilitySummary)
   *
   * @param string $parent Required. The name of the project to get a
   * vulnerability summary for in the form of `projects/[PROJECT_ID]`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter The filter expression.
   * @return VulnerabilityOccurrencesSummary
   * @throws \Google\Service\Exception
   */
  public function getVulnerabilitySummary($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('getVulnerabilitySummary', [$params], VulnerabilityOccurrencesSummary::class);
  }
  /**
   * Lists occurrences for the specified project.
   * (occurrences.listProjectsLocationsOccurrences)
   *
   * @param string $parent Required. The name of the project to list occurrences
   * for in the form of `projects/[PROJECT_ID]`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter The filter expression.
   * @opt_param int pageSize Number of occurrences to return in the list. Must be
   * positive. Max allowed page size is 1000. If not specified, page size defaults
   * to 20.
   * @opt_param string pageToken Token to provide to skip to a particular spot in
   * the list.
   * @return ListOccurrencesResponse
   * @throws \Google\Service\Exception
   */
  public function listProjectsLocationsOccurrences($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListOccurrencesResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsOccurrences::class, 'Google_Service_ContainerAnalysis_Resource_ProjectsLocationsOccurrences');
