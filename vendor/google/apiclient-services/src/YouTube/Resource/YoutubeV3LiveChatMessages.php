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

namespace Google\Service\YouTube\Resource;

use Google\Service\YouTube\LiveChatMessage;

/**
 * The "messages" collection of methods.
 * Typical usage is:
 *  <code>
 *   $youtubeService = new Google\Service\YouTube(...);
 *   $messages = $youtubeService->youtube_v3_liveChat_messages;
 *  </code>
 */
class YoutubeV3LiveChatMessages extends \Google\Service\Resource
{
  /**
   * Transition a durable chat event. (messages.transition)
   *
   * @param array $optParams Optional parameters.
   *
   * @opt_param string id The ID that uniquely identify the chat message event to
   * transition.
   * @opt_param string status The status to which the chat event is going to
   * transition.
   * @return LiveChatMessage
   * @throws \Google\Service\Exception
   */
  public function transition($optParams = [])
  {
    $params = [];
    $params = array_merge($params, $optParams);
    return $this->call('transition', [$params], LiveChatMessage::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(YoutubeV3LiveChatMessages::class, 'Google_Service_YouTube_Resource_YoutubeV3LiveChatMessages');
