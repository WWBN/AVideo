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

use Google\Service\HangoutsChat\ChatEmpty;
use Google\Service\HangoutsChat\Message;

/**
 * The "messages" collection of methods.
 * Typical usage is:
 *  <code>
 *   $chatService = new Google\Service\HangoutsChat(...);
 *   $messages = $chatService->spaces_messages;
 *  </code>
 */
class SpacesMessages extends \Google\Service\Resource
{
  /**
   * Creates a message. For example usage, see [Create a message](https://develope
   * rs.google.com/chat/api/guides/crudl/messages#create_a_message). Requires
   * [authentication](https://developers.google.com/chat/api/guides/auth). Fully
   * supports [service account
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts). Supports [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users) as
   * part of the [Google Workspace Developer Preview
   * Program](https://developers.google.com/workspace/preview), which grants early
   * access to certain features. [User
   * authentication](https://developers.google.com/chat/api/guides/auth/users)
   * requires the `chat.messages` or `chat.messages.create` authorization scope.
   * Because Chat provides authentication for
   * [webhooks](https://developers.google.com/chat/how-tos/webhooks) as part of
   * the URL that's generated when a webhook is registered, webhooks can create
   * messages without a service account or user authentication. (messages.create)
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
   * [Start or reply to a message thread](https://developers.google.com/chat/api/g
   * uides/crudl/messages#start_or_reply_to_a_message_thread).
   * @return Message
   */
  public function create($parent, Message $postBody, $optParams = [])
  {
    $params = ['parent' => $parent, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], Message::class);
  }
  /**
   * Deletes a message. For example usage, see [Delete a message](https://develope
   * rs.google.com/chat/api/guides/crudl/messages#delete_a_message). Requires
   * [authentication](https://developers.google.com/chat/api/guides/auth). Fully
   * supports [service account
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts). Supports [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users) as
   * part of the [Google Workspace Developer Preview
   * Program](https://developers.google.com/workspace/preview), which grants early
   * access to certain features. [User
   * authentication](https://developers.google.com/chat/api/guides/auth/users)
   * requires the `chat.messages` authorization scope. (messages.delete)
   *
   * @param string $name Required. Resource name of the message to be deleted, in
   * the form "spaces/messages" Example:
   * spaces/AAAAAAAAAAA/messages/BBBBBBBBBBB.BBBBBBBBBBB
   * @param array $optParams Optional parameters.
   * @return ChatEmpty
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], ChatEmpty::class);
  }
  /**
   * Returns a message. For example usage, see [Read a message](https://developers
   * .google.com/chat/api/guides/crudl/messages#read_a_message). Requires
   * [authentication](https://developers.google.com/chat/api/guides/auth). Fully
   * supports [Service account
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts). Supports [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users) as
   * part of the [Google Workspace Developer Preview
   * Program](https://developers.google.com/workspace/preview), which grants early
   * access to certain features. [User
   * authentication](https://developers.google.com/chat/api/guides/auth/users)
   * requires the `chat.messages` or `chat.messages.readonly` authorization scope.
   * Note: Might return a message from a blocked member or space. (messages.get)
   *
   * @param string $name Required. Resource name of the message to retrieve.
   * Format: spaces/{space}/messages/{message} If the message begins with
   * `client-`, then it has a custom name assigned by a Chat app that created it
   * with the Chat REST API. That Chat app (but not others) can pass the custom
   * name to get, update, or delete the message. To learn more, see [create and
   * name a message] (https://developers.google.com/chat/api/guides/crudl/messages
   * #name_a_created_message).
   * @param array $optParams Optional parameters.
   * @return Message
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], Message::class);
  }
  /**
   * Updates a message. There's a difference between `patch` and `update` methods.
   * The `patch` method uses a `patch` request while the `update` method uses a
   * `put` request. We recommend using the `patch` method. For example usage, see
   * [Update a message](https://developers.google.com/chat/api/guides/crudl/messag
   * es#update_a_message). Requires
   * [authentication](https://developers.google.com/chat/api/guides/auth/). Fully
   * supports [service account
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts). Supports [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users) as
   * part of the [Google Workspace Developer Preview
   * Program](https://developers.google.com/workspace/preview), which grants early
   * access to certain features. [User
   * authentication](https://developers.google.com/chat/api/guides/auth/users)
   * requires the `chat.messages` authorization scope. (messages.patch)
   *
   * @param string $name Resource name in the form `spaces/messages`. Example:
   * `spaces/AAAAAAAAAAA/messages/BBBBBBBBBBB.BBBBBBBBBBB`
   * @param Message $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool allowMissing Optional. If `true` and the message is not
   * found, a new message is created and `updateMask` is ignored. The specified
   * message ID must be [client-assigned](https://developers.google.com/chat/api/g
   * uides/crudl/messages#name_a_created_message) or the request fails.
   * @opt_param string updateMask Required. The field paths to update. Separate
   * multiple values with commas. Currently supported field paths: - text - cards
   * (Requires [service account authentication](/chat/api/guides/auth/service-
   * accounts).) - cards_v2
   * @return Message
   */
  public function patch($name, Message $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], Message::class);
  }
  /**
   * Updates a message. There's a difference between `patch` and `update` methods.
   * The `patch` method uses a `patch` request while the `update` method uses a
   * `put` request. We recommend using the `patch` method. For example usage, see
   * [Update a message](https://developers.google.com/chat/api/guides/crudl/messag
   * es#update_a_message). Requires
   * [authentication](https://developers.google.com/chat/api/guides/auth/). Fully
   * supports [service account
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts). Supports [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users) as
   * part of the [Google Workspace Developer Preview
   * Program](https://developers.google.com/workspace/preview), which grants early
   * access to certain features. [User
   * authentication](https://developers.google.com/chat/api/guides/auth/users)
   * requires the `chat.messages` authorization scope. (messages.update)
   *
   * @param string $name Resource name in the form `spaces/messages`. Example:
   * `spaces/AAAAAAAAAAA/messages/BBBBBBBBBBB.BBBBBBBBBBB`
   * @param Message $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool allowMissing Optional. If `true` and the message is not
   * found, a new message is created and `updateMask` is ignored. The specified
   * message ID must be [client-assigned](https://developers.google.com/chat/api/g
   * uides/crudl/messages#name_a_created_message) or the request fails.
   * @opt_param string updateMask Required. The field paths to update. Separate
   * multiple values with commas. Currently supported field paths: - text - cards
   * (Requires [service account authentication](/chat/api/guides/auth/service-
   * accounts).) - cards_v2
   * @return Message
   */
  public function update($name, Message $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('update', [$params], Message::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SpacesMessages::class, 'Google_Service_HangoutsChat_Resource_SpacesMessages');
