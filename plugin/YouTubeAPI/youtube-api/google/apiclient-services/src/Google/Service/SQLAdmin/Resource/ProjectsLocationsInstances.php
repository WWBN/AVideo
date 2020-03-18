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

/**
 * The "instances" collection of methods.
 * Typical usage is:
 *  <code>
 *   $sqlService = new Google_Service_SQLAdmin(...);
 *   $instances = $sqlService->instances;
 *  </code>
 */
class Google_Service_SQLAdmin_Resource_ProjectsLocationsInstances extends Google_Service_Resource
{
  /**
   * Reschedules the maintenance on the given instance.
   * (instances.rescheduleMaintenance)
   *
   * @param string $parent The parent resource where Cloud SQL reshedule this
   * database instance's maintenance. Format:
   * projects/{project}/locations/{location}/instances/{instance}
   * @param Google_Service_SQLAdmin_SqlInstancesRescheduleMaintenanceRequestBody $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string instance Cloud SQL instance ID. This does not include the
   * project ID.
   * @opt_param string project ID of the project that contains the instance.
   * @return Google_Service_SQLAdmin_Operation
   */
  public function rescheduleMaintenance($parent, Google_Service_SQLAdmin_SqlInstancesRescheduleMaintenanceRequestBody $postBody, $optParams = array())
  {
    $params = array('parent' => $parent, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('rescheduleMaintenance', array($params), "Google_Service_SQLAdmin_Operation");
  }
  /**
   * Start External master migration. (instances.startExternalSync)
   *
   * @param string $parent The parent resource where Cloud SQL starts this
   * database instance external sync. Format:
   * projects/{project}/locations/{location}/instances/{instance}
   * @param array $optParams Optional parameters.
   *
   * @opt_param string project ID of the project that contains the first
   * generation instance.
   * @opt_param string syncMode External sync mode
   * @opt_param string instance Cloud SQL instance ID. This does not include the
   * project ID.
   * @return Google_Service_SQLAdmin_Operation
   */
  public function startExternalSync($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('startExternalSync', array($params), "Google_Service_SQLAdmin_Operation");
  }
  /**
   * Verify External master external sync settings.
   * (instances.verifyExternalSyncSettings)
   *
   * @param string $parent The parent resource where Cloud SQL verifies this
   * database instance external sync settings. Format:
   * projects/{project}/locations/{location}/instances/{instance}
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool verifyConnectionOnly Flag to enable verifying connection only
   * @opt_param string instance Cloud SQL instance ID. This does not include the
   * project ID.
   * @opt_param string project Project ID of the project that contains the
   * instance.
   * @opt_param string syncMode External sync mode
   * @return Google_Service_SQLAdmin_SqlInstancesVerifyExternalSyncSettingsResponse
   */
  public function verifyExternalSyncSettings($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('verifyExternalSyncSettings', array($params), "Google_Service_SQLAdmin_SqlInstancesVerifyExternalSyncSettingsResponse");
  }
}
