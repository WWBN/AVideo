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
 * The "detections" collection of methods.
 * Typical usage is:
 *  <code>
 *   $translateService = new Google_Service_Translate(...);
 *   $detections = $translateService->detections;
 *  </code>
 */
class Google_Service_Translate_Resource_Detections extends Google_Service_Resource
{
  /**
   * Detect the language of text. (detections.listDetections)
   *
   * @param string|array $q The text to detect
   * @param array $optParams Optional parameters.
   * @return Google_Service_Translate_DetectionsListResponse
   */
  public function listDetections($q, $optParams = array())
  {
    $params = array('q' => $q);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Translate_DetectionsListResponse");
  }
}
