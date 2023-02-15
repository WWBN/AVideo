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

namespace Google\Service\CloudDataplex\Resource;

use Google\Service\CloudDataplex\GoogleCloudDataplexV1DataScanJob;
use Google\Service\CloudDataplex\GoogleCloudDataplexV1ListDataScanJobsResponse;

/**
 * The "jobs" collection of methods.
 * Typical usage is:
 *  <code>
 *   $dataplexService = new Google\Service\CloudDataplex(...);
 *   $jobs = $dataplexService->projects_locations_dataScans_jobs;
 *  </code>
 */
class ProjectsLocationsDataScansJobs extends \Google\Service\Resource
{
  /**
   * Gets a DataScanJob resource. (jobs.get)
   *
   * @param string $name Required. The resource name of the DataScanJob: projects/
   * {project}/locations/{location_id}/dataScans/{data_scan_id}/dataScanJobs/{data
   * _scan_job_id} where project refers to a project_id or project_number and
   * location_id refers to a GCP region.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string view Optional. Select the DataScanJob view to return.
   * Defaults to BASIC.
   * @return GoogleCloudDataplexV1DataScanJob
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], GoogleCloudDataplexV1DataScanJob::class);
  }
  /**
   * Lists DataScanJobs under the given DataScan.
   * (jobs.listProjectsLocationsDataScansJobs)
   *
   * @param string $parent Required. The resource name of the parent environment:
   * projects/{project}/locations/{location_id}/dataScans/{data_scan_id} where
   * project refers to a project_id or project_number and location_id refers to a
   * GCP region.
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Optional. Maximum number of DataScanJobs to return.
   * The service may return fewer than this value. If unspecified, at most 10
   * DataScanJobs will be returned. The maximum value is 1000; values above 1000
   * will be coerced to 1000.
   * @opt_param string pageToken Optional. Page token received from a previous
   * ListDataScanJobs call. Provide this to retrieve the subsequent page. When
   * paginating, all other parameters provided to ListDataScanJobs must match the
   * call that provided the page token.
   * @return GoogleCloudDataplexV1ListDataScanJobsResponse
   */
  public function listProjectsLocationsDataScansJobs($parent, $optParams = [])
  {
    $params = ['parent' => $parent];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], GoogleCloudDataplexV1ListDataScanJobsResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ProjectsLocationsDataScansJobs::class, 'Google_Service_CloudDataplex_Resource_ProjectsLocationsDataScansJobs');
