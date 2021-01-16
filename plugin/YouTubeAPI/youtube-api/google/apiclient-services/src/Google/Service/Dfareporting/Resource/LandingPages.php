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
 * The "landingPages" collection of methods.
 * Typical usage is:
 *  <code>
 *   $dfareportingService = new Google_Service_Dfareporting(...);
 *   $landingPages = $dfareportingService->landingPages;
 *  </code>
 */
class Google_Service_Dfareporting_Resource_LandingPages extends Google_Service_Resource
{
  /**
   * Deletes an existing campaign landing page. (landingPages.delete)
   *
   * @param string $profileId User profile ID associated with this request.
   * @param string $campaignId Landing page campaign ID.
   * @param string $id Landing page ID.
   * @param array $optParams Optional parameters.
   */
  public function delete($profileId, $campaignId, $id, $optParams = array())
  {
    $params = array('profileId' => $profileId, 'campaignId' => $campaignId, 'id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }
  /**
   * Gets one campaign landing page by ID. (landingPages.get)
   *
   * @param string $profileId User profile ID associated with this request.
   * @param string $campaignId Landing page campaign ID.
   * @param string $id Landing page ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Dfareporting_LandingPage
   */
  public function get($profileId, $campaignId, $id, $optParams = array())
  {
    $params = array('profileId' => $profileId, 'campaignId' => $campaignId, 'id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Dfareporting_LandingPage");
  }
  /**
   * Inserts a new landing page for the specified campaign. (landingPages.insert)
   *
   * @param string $profileId User profile ID associated with this request.
   * @param string $campaignId Landing page campaign ID.
   * @param Google_Service_Dfareporting_LandingPage $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Dfareporting_LandingPage
   */
  public function insert($profileId, $campaignId, Google_Service_Dfareporting_LandingPage $postBody, $optParams = array())
  {
    $params = array('profileId' => $profileId, 'campaignId' => $campaignId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Dfareporting_LandingPage");
  }
  /**
   * Retrieves the list of landing pages for the specified campaign.
   * (landingPages.listLandingPages)
   *
   * @param string $profileId User profile ID associated with this request.
   * @param string $campaignId Landing page campaign ID.
   * @param array $optParams Optional parameters.
   * @return Google_Service_Dfareporting_LandingPagesListResponse
   */
  public function listLandingPages($profileId, $campaignId, $optParams = array())
  {
    $params = array('profileId' => $profileId, 'campaignId' => $campaignId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Dfareporting_LandingPagesListResponse");
  }
  /**
   * Updates an existing campaign landing page. This method supports patch
   * semantics. (landingPages.patch)
   *
   * @param string $profileId User profile ID associated with this request.
   * @param string $campaignId Landing page campaign ID.
   * @param string $id Landing page ID.
   * @param Google_Service_Dfareporting_LandingPage $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Dfareporting_LandingPage
   */
  public function patch($profileId, $campaignId, $id, Google_Service_Dfareporting_LandingPage $postBody, $optParams = array())
  {
    $params = array('profileId' => $profileId, 'campaignId' => $campaignId, 'id' => $id, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Dfareporting_LandingPage");
  }
  /**
   * Updates an existing campaign landing page. (landingPages.update)
   *
   * @param string $profileId User profile ID associated with this request.
   * @param string $campaignId Landing page campaign ID.
   * @param Google_Service_Dfareporting_LandingPage $postBody
   * @param array $optParams Optional parameters.
   * @return Google_Service_Dfareporting_LandingPage
   */
  public function update($profileId, $campaignId, Google_Service_Dfareporting_LandingPage $postBody, $optParams = array())
  {
    $params = array('profileId' => $profileId, 'campaignId' => $campaignId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Dfareporting_LandingPage");
  }
}
