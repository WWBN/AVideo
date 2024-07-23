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

use Google\Service\ContainerAnalysis\ListNotesResponse;
use Google\Service\ContainerAnalysis\Note;

/**
 * The "notes" collection of methods.
 * Typical usage is:
 *  <code>
 *   $containeranalysisService = new Google\Service\ContainerAnalysis(...);
 *   $notes = $containeranalysisService->projects_locations_notes;
 *  </code>
 */
class ProjectsLocationsNotes extends \Google\Service\Resource
{
  /**
   * Gets the specified note. (notes.get)
   *
   * @param string $name Required. The name of the note in the form of
   * `projects/[PROVIDER_ID]/notes/[NOTE_ID]`.
   * @param array $optParams Optional parameters.
   * @return Note
   * @throws \Google\Service\Exception
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], Note::class);
  }
  /**
   * Lists notes for the specified project. (notes.listProjectsLocationsNotes)
   *
   * @param string $parent Required. The name of the project to list notes for in
   * the form of `projects/[PROJECT_ID]`.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter The filter expression.
   * @opt_param int pageSize Number of notes to return in the list. Must be
   * positive. Max allowed page size is 1000. If not specified, page size defaults
   * to 20.
   * @opt_param string pageToken Token to provide to skip to a particular spot in
   * the list.
   * @return ListNotesResponse
   * @throws \Google\Service\Exception
   */
  public function listProjectsLocationsNotes($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListNotesResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsNotes::class, 'Google_Service_ContainerAnalysis_Resource_ProjectsLocationsNotes');
