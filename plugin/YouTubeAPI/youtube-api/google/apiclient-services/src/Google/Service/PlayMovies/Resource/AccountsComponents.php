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
 * The "components" collection of methods.
 * Typical usage is:
 *  <code>
 *   $playmoviespartnerService = new Google_Service_PlayMovies(...);
 *   $components = $playmoviespartnerService->components;
 *  </code>
 */
class Google_Service_PlayMovies_Resource_AccountsComponents extends Google_Service_Resource
{
  /**
   * List Components owned or managed by the partner. See _Authentication and
   * Authorization rules_ and _List methods rules_ for more information about this
   * method. (components.listAccountsComponents)
   *
   * @param string $accountId REQUIRED. See _General rules_ for more information
   * about this field.
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize See _List methods rules_ for info about this field.
   * @opt_param string pageToken See _List methods rules_ for info about this
   * field.
   * @opt_param string pphNames See _List methods rules_ for info about this
   * field.
   * @opt_param string studioNames See _List methods rules_ for info about this
   * field.
   * @opt_param string titleLevelEidr Filter Components that match a given title-
   * level EIDR.
   * @opt_param string editLevelEidr Filter Components that match a given edit-
   * level EIDR.
   * @opt_param string status Filter Components that match one of the given
   * status.
   * @opt_param string customId Filter Components that match a case-insensitive
   * partner-specific custom id.
   * @opt_param string inventoryId InventoryID available in Common Manifest.
   * @opt_param string presentationId PresentationID available in Common Manifest.
   * @opt_param string playableSequenceId PlayableSequenceID available in Common
   * Manifest.
   * @opt_param string elId Experience ID, as defined by Google.
   * @opt_param string altCutId Filter Components that match a case-insensitive,
   * partner-specific Alternative Cut ID.
   * @opt_param string filename Filter Components that match a case-insensitive
   * substring of the physical name of the delivered file.
   * @return Google_Service_PlayMovies_ListComponentsResponse
   */
  public function listAccountsComponents($accountId, $optParams = array())
  {
    $params = array('accountId' => $accountId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_PlayMovies_ListComponentsResponse");
  }
}
