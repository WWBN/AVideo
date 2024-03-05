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

namespace Google\Service;

use Google\Client;

/**
 * Service definition for DataPortability (v1beta).
 *
 * <p>
 * The Data Portability API lets you build applications that request
 * authorization from a user to move a copy of data from Google services into
 * your application. This enables data portability and facilitates switching
 * services.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://developers.google.com/data-portability" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class DataPortability extends \Google\Service
{
  /** Move a copy of messages between you and the businesses you have conversations with across Google services.. */
  const DATAPORTABILITY_BUSINESSMESSAGING_CONVERSATIONS =
      "https://www.googleapis.com/auth/dataportability.businessmessaging.conversations";
  /** Move a copy of your pinned trips on Maps.. */
  const DATAPORTABILITY_MAPS_COMMUTE_ROUTES =
      "https://www.googleapis.com/auth/dataportability.maps.commute_routes";
  /** Move a copy of your commute settings on Maps.. */
  const DATAPORTABILITY_MAPS_COMMUTE_SETTINGS =
      "https://www.googleapis.com/auth/dataportability.maps.commute_settings";
  /** Move a copy of your electric vehicle profile on Maps.. */
  const DATAPORTABILITY_MAPS_EV_PROFILE =
      "https://www.googleapis.com/auth/dataportability.maps.ev_profile";
  /** Move a copy of your updates to places on Maps.. */
  const DATAPORTABILITY_MAPS_OFFERING_CONTRIBUTIONS =
      "https://www.googleapis.com/auth/dataportability.maps.offering_contributions";
  /** Move a copy of the photos and videos you posted on Maps.. */
  const DATAPORTABILITY_MAPS_PHOTOS_VIDEOS =
      "https://www.googleapis.com/auth/dataportability.maps.photos_videos";
  /** Move a copy of your reviews and posts on Maps.. */
  const DATAPORTABILITY_MAPS_REVIEWS =
      "https://www.googleapis.com/auth/dataportability.maps.reviews";
  /** Move a copy of your Starred places list on Maps.. */
  const DATAPORTABILITY_MAPS_STARRED_PLACES =
      "https://www.googleapis.com/auth/dataportability.maps.starred_places";
  /** Move a copy of your Maps activity.. */
  const DATAPORTABILITY_MYACTIVITY_MAPS =
      "https://www.googleapis.com/auth/dataportability.myactivity.maps";
  /** Move a copy of your Google Search activity.. */
  const DATAPORTABILITY_MYACTIVITY_SEARCH =
      "https://www.googleapis.com/auth/dataportability.myactivity.search";
  /** Move a copy of your Shopping activity.. */
  const DATAPORTABILITY_MYACTIVITY_SHOPPING =
      "https://www.googleapis.com/auth/dataportability.myactivity.shopping";
  /** Move a copy of your YouTube activity.. */
  const DATAPORTABILITY_MYACTIVITY_YOUTUBE =
      "https://www.googleapis.com/auth/dataportability.myactivity.youtube";
  /** Move a copy of your shipping information on Shopping.. */
  const DATAPORTABILITY_SHOPPING_ADDRESSES =
      "https://www.googleapis.com/auth/dataportability.shopping.addresses";
  /** Move a copy of reviews you wrote about products or online stores on Google Search.. */
  const DATAPORTABILITY_SHOPPING_REVIEWS =
      "https://www.googleapis.com/auth/dataportability.shopping.reviews";
  /** Move a copy of information about your YouTube channel.. */
  const DATAPORTABILITY_YOUTUBE_CHANNEL =
      "https://www.googleapis.com/auth/dataportability.youtube.channel";
  /** Move a copy of your YouTube comments.. */
  const DATAPORTABILITY_YOUTUBE_COMMENTS =
      "https://www.googleapis.com/auth/dataportability.youtube.comments";
  /** Move a copy of your YouTube messages in live chat.. */
  const DATAPORTABILITY_YOUTUBE_LIVE_CHAT =
      "https://www.googleapis.com/auth/dataportability.youtube.live_chat";
  /** Move a copy of your uploaded YouTube music tracks and your YouTube music library.. */
  const DATAPORTABILITY_YOUTUBE_MUSIC =
      "https://www.googleapis.com/auth/dataportability.youtube.music";
  /** Move a copy of your YouTube private playlists.. */
  const DATAPORTABILITY_YOUTUBE_PRIVATE_PLAYLISTS =
      "https://www.googleapis.com/auth/dataportability.youtube.private_playlists";
  /** Move a copy of your private YouTube videos and information about them.. */
  const DATAPORTABILITY_YOUTUBE_PRIVATE_VIDEOS =
      "https://www.googleapis.com/auth/dataportability.youtube.private_videos";
  /** Move a copy of your public YouTube playlists.. */
  const DATAPORTABILITY_YOUTUBE_PUBLIC_PLAYLISTS =
      "https://www.googleapis.com/auth/dataportability.youtube.public_playlists";
  /** Move a copy of your public YouTube videos and information about them.. */
  const DATAPORTABILITY_YOUTUBE_PUBLIC_VIDEOS =
      "https://www.googleapis.com/auth/dataportability.youtube.public_videos";
  /** Move a copy of your YouTube shopping wishlists, and wishlist items.. */
  const DATAPORTABILITY_YOUTUBE_SHOPPING =
      "https://www.googleapis.com/auth/dataportability.youtube.shopping";
  /** Move a copy of your YouTube channel subscriptions, even if they're private.. */
  const DATAPORTABILITY_YOUTUBE_SUBSCRIPTIONS =
      "https://www.googleapis.com/auth/dataportability.youtube.subscriptions";
  /** Move a copy of your unlisted YouTube playlists.. */
  const DATAPORTABILITY_YOUTUBE_UNLISTED_PLAYLISTS =
      "https://www.googleapis.com/auth/dataportability.youtube.unlisted_playlists";
  /** Move a copy of your unlisted YouTube videos and information about them.. */
  const DATAPORTABILITY_YOUTUBE_UNLISTED_VIDEOS =
      "https://www.googleapis.com/auth/dataportability.youtube.unlisted_videos";

  public $archiveJobs;
  public $authorization;
  public $portabilityArchive;

  /**
   * Constructs the internal representation of the DataPortability service.
   *
   * @param Client|array $clientOrConfig The client used to deliver requests, or a
   *                                     config array to pass to a new Client instance.
   * @param string $rootUrl The root URL used for requests to the service.
   */
  public function __construct($clientOrConfig = [], $rootUrl = null)
  {
    parent::__construct($clientOrConfig);
    $this->rootUrl = $rootUrl ?: 'https://dataportability.googleapis.com/';
    $this->servicePath = '';
    $this->batchPath = 'batch';
    $this->version = 'v1beta';
    $this->serviceName = 'dataportability';

    $this->archiveJobs = new DataPortability\Resource\ArchiveJobs(
        $this,
        $this->serviceName,
        'archiveJobs',
        [
          'methods' => [
            'getPortabilityArchiveState' => [
              'path' => 'v1beta/{+name}',
              'httpMethod' => 'GET',
              'parameters' => [
                'name' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
              ],
            ],'retry' => [
              'path' => 'v1beta/{+name}:retry',
              'httpMethod' => 'POST',
              'parameters' => [
                'name' => [
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ],
              ],
            ],
          ]
        ]
    );
    $this->authorization = new DataPortability\Resource\Authorization(
        $this,
        $this->serviceName,
        'authorization',
        [
          'methods' => [
            'reset' => [
              'path' => 'v1beta/authorization:reset',
              'httpMethod' => 'POST',
              'parameters' => [],
            ],
          ]
        ]
    );
    $this->portabilityArchive = new DataPortability\Resource\PortabilityArchive(
        $this,
        $this->serviceName,
        'portabilityArchive',
        [
          'methods' => [
            'initiate' => [
              'path' => 'v1beta/portabilityArchive:initiate',
              'httpMethod' => 'POST',
              'parameters' => [],
            ],
          ]
        ]
    );
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(DataPortability::class, 'Google_Service_DataPortability');
