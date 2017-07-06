<?php
/*
 * Copyright 2016 Google Inc.
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
 * The "batchReports" collection of methods.
 * Typical usage is:
 *  <code>
 *   $youtubeAnalyticsService = new Google_Service_YouTubeAnalytics(...);
 *   $batchReports = $youtubeAnalyticsService->batchReports;
 *  </code>
 */
class Google_Service_YouTubeAnalytics_Resource_BatchReports extends Google_Service_Resource
{
  /**
   * Retrieves a list of processed batch reports. (batchReports.listBatchReports)
   *
   * @param string $batchReportDefinitionId The batchReportDefinitionId parameter
   * specifies the ID of the batch reportort definition for which you are
   * retrieving reports.
   * @param string $onBehalfOfContentOwner The onBehalfOfContentOwner parameter
   * identifies the content owner that the user is acting on behalf of.
   * @param array $optParams Optional parameters.
   * @return Google_Service_YouTubeAnalytics_BatchReportList
   */
  public function listBatchReports($batchReportDefinitionId, $onBehalfOfContentOwner, $optParams = array())
  {
    $params = array('batchReportDefinitionId' => $batchReportDefinitionId, 'onBehalfOfContentOwner' => $onBehalfOfContentOwner);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTubeAnalytics_BatchReportList");
  }
}
