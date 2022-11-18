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

namespace Google\Service\HangoutsChat\Resource;

use Google\Service\HangoutsChat\ListSpacesResponse;
use Google\Service\HangoutsChat\Message;
use Google\Service\HangoutsChat\Space;

/**
 * The "spaces" collection of methods.
 * Typical usage is:
 *  <code>
 *   $chatService = new Google\Service\HangoutsChat(...);
 *   $spaces = $chatService->spaces;
 *  </code>
 */
class Spaces extends \Google\Service\Resource
{
  /**
   * Returns a space. Requires [service account
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts). (spaces.get)
   *
   * @param string $name Required. Resource name of the space, in the form
   * "spaces". Format: spaces/{space}
   * @param array $optParams Optional parameters.
   * @return Space
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], Space::class);
  }
  /**
   * Lists spaces the caller is a member of. Requires [service account
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts).  (spaces.listSpaces)
   *
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Optional. The maximum number of spaces to return. The
   * service may return fewer than this value. If unspecified, at most 100 spaces
   * are returned. The maximum value is 1000; values above 1000 are coerced to
   * 1000. Negative values return an INVALID_ARGUMENT error.
   * @opt_param string pageToken Optional. A page token, received from a previous
   * list spaces call. Provide this to retrieve the subsequent page. When
   * paginating, all other parameters provided must match the call that provided
   * the page token.
   * @return ListSpacesResponse
   */
  public function listSpaces($optParams = [])
  {
    $params = [];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListSpacesResponse::class);
  }
  /**
   * Legacy path for creating message. Calling these will result in a BadRequest
   * response. (spaces.webhooks)
   *
   * @param string $parent Required. The resource name of the space in which to
   * create a message. Format: spaces/{space}
   * @param Message $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string messageId Optional. A custom name for a Chat message
   * assigned at creation. Must start with `client-` and contain only lowercase
   * letters, numbers, and hyphens up to 63 characters in length. Specify this
   * field to get, update, or delete the message with the specified value. For
   * example usage, see [Name a created message](https://developers.google.com/cha
   * t/api/guides/crudl/messages#name_a_created_message).
   * @opt_param string messageReplyOption Optional. Specifies whether a message
   * starts a thread or replies to one. Only supported in named spaces.
   * @opt_param string requestId Optional. A unique request ID for this message.
   * Specifying an existing request ID returns the message created with that ID
   * instead of creating a new message.
   * @opt_param string threadKey Optional. Deprecated: Use thread.thread_key
   * instead. Opaque thread identifier. To start or add to a thread, create a
   * message and specify a `threadKey` or the thread.name. For example usage, see
   * [Start or reply to a message
   * thread](/chat/api/guides/crudl/messages#start_or_reply_to_a_message_thread).
   * @return Message
   */
  public function webhooks($parent, Message $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('webhooks', [$params], Message::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Spaces::class, 'Google_Service_HangoutsChat_Resource_Spaces');
