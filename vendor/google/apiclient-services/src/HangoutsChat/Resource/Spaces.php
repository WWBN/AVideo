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
   * Returns a space. Requires
   * [authentication](https://developers.google.com/chat/api/guides/auth). Fully
   * supports [service account
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts). Supports [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users) as
   * part of the [Google Workspace Developer Preview
   * Program](https://developers.google.com/workspace/preview), which grants early
   * access to certain features. [User
   * authentication](https://developers.google.com/chat/api/guides/auth/users)
   * requires the `chat.spaces` or `chat.spaces.readonly` authorization scope.
   * (spaces.get)
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
   * Lists spaces the caller is a member of. Requires
   * [authentication](https://developers.google.com/chat/api/guides/auth). Fully
   * supports [service account
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts). Supports [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users) as
   * part of the [Google Workspace Developer Preview
   * Program](https://developers.google.com/workspace/preview), which grants early
   * access to certain features. [User
   * authentication](https://developers.google.com/chat/api/guides/auth/users)
   * requires the `chat.spaces` or `chat.spaces.readonly` authorization scope.
   * Lists spaces visible to the caller or authenticated user. Group chats and DMs
   * aren't listed until the first message is sent. (spaces.listSpaces)
   *
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Optional. The maximum number of spaces to return. The
   * service may return fewer than this value. If unspecified, at most 100 spaces
   * are returned. The maximum value is 1000; values above 1000 are coerced to
   * 1000. Negative values return an INVALID_ARGUMENT error.
   * @opt_param string pageToken Optional. A page token, received from a previous
   * list spaces call. Provide this to retrieve the subsequent page. When
   * paginating, the filter value should match the call that provided the page
   * token. Passing a different value may lead to unexpected results.
   * @return ListSpacesResponse
   */
  public function listSpaces($optParams = [])
  {
    $params = [];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListSpacesResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Spaces::class, 'Google_Service_HangoutsChat_Resource_Spaces');
