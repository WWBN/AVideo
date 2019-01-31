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
 * The "rubicon" collection of methods.
 * Typical usage is:
 *  <code>
 *   $doubleclickbidmanagerService = new Google_Service_DoubleClickBidManager(...);
 *   $rubicon = $doubleclickbidmanagerService->rubicon;
 *  </code>
 */
class Google_Service_DoubleClickBidManager_Resource_Rubicon extends Google_Service_Resource
{
  /**
   * Update proposal upon actions of Rubicon publisher.
   * (rubicon.notifyproposalchange)
   *
   * @param Google_Service_DoubleClickBidManager_NotifyProposalChangeRequest $postBody
   * @param array $optParams Optional parameters.
   */
  public function notifyproposalchange(Google_Service_DoubleClickBidManager_NotifyProposalChangeRequest $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('notifyproposalchange', array($params));
  }
}
