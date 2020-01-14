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
 * The "type" collection of methods.
 * Typical usage is:
 *  <code>
 *   $playmoviespartnerService = new Google_Service_PlayMovies(...);
 *   $type = $playmoviespartnerService->type;
 *  </code>
 */
class Google_Service_PlayMovies_Resource_AccountsComponentsType extends Google_Service_Resource
{
  /**
   * Get a Component given its id. (type.get)
   *
   * @param string $accountId REQUIRED. See _General rules_ for more information
   * about this field.
   * @param string $componentId REQUIRED. Component ID.
   * @param string $type REQUIRED. Component Type.
   * @param array $optParams Optional parameters.
   * @return Google_Service_PlayMovies_Component
   */
  public function get($accountId, $componentId, $type, $optParams = array())
  {
    $params = array('accountId' => $accountId, 'componentId' => $componentId, 'type' => $type);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_PlayMovies_Component");
  }
}
