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
 * The "projects" collection of methods.
 * Typical usage is:
 *  <code>
 *   $cloudtraceService = new Google_Service_CloudTrace(...);
 *   $projects = $cloudtraceService->projects;
 *  </code>
 */
class Google_Service_CloudTrace_Resource_Projects extends Google_Service_Resource
{
  /**
   * Sends new traces to Stackdriver Trace or updates existing traces. If the ID
   * of a trace that you send matches that of an existing trace, any fields in the
   * existing trace and its spans are overwritten by the provided values, and any
   * new fields provided are merged with the existing trace data. If the ID does
   * not match, a new trace is created. (projects.patchTraces)
   *
   * @param string $projectId ID of the Cloud project where the trace data is
   * stored.
   * @param Google_Service_CloudTrace_Traces $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_CloudTrace_CloudtraceEmpty
   */
  public function patchTraces($projectId, Google_Service_CloudTrace_Traces $postBody, $optParams = array())
  {
    $params = array('projectId' => $projectId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patchTraces', array($params), "Google_Service_CloudTrace_CloudtraceEmpty");
  }
}
