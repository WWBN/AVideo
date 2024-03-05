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
use Google\Service\HangoutsChat\CompleteImportSpaceRequest;
use Google\Service\HangoutsChat\CompleteImportSpaceResponse;
use Google\Service\HangoutsChat\ListSpacesResponse;
use Google\Service\HangoutsChat\SetUpSpaceRequest;
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
   * Completes the [import
   * process](https://developers.google.com/chat/api/guides/import-data) for the
   * specified space and makes it visible to users. Requires app authentication
   * and domain-wide delegation. For more information, see [Authorize Google Chat
   * apps to import data](https://developers.google.com/chat/api/guides/authorize-
   * import). (spaces.completeImport)
   *
   * @param string $name Required. Resource name of the import mode space. Format:
   * `spaces/{space}`
   * @param CompleteImportSpaceRequest $postBody
   * @param array $optParams Optional parameters.
   * @return CompleteImportSpaceResponse
   * @throws \Google\Service\Exception
   */
  public function completeImport($name, CompleteImportSpaceRequest $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('completeImport', [$params], CompleteImportSpaceResponse::class);
  }
  /**
   * Creates a named space. Spaces grouped by topics aren't supported. For an
   * example, see [Create a
   * space](https://developers.google.com/chat/api/guides/v1/spaces/create). If
   * you receive the error message `ALREADY_EXISTS` when creating a space, try a
   * different `displayName`. An existing space within the Google Workspace
   * organization might already use this display name. Requires [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users).
   * (spaces.create)
   *
   * @param Space $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string requestId Optional. A unique identifier for this request. A
   * random UUID is recommended. Specifying an existing request ID returns the
   * space created with that ID instead of creating a new space. Specifying an
   * existing request ID from the same Chat app with a different authenticated
   * user returns an error.
   * @return Space
   * @throws \Google\Service\Exception
   */
  public function create(Space $postBody, $optParams = [])
  {
    $params = ['postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('create', [$params], Space::class);
  }
  /**
   * Deletes a named space. Always performs a cascading delete, which means that
   * the space's child resources—like messages posted in the space and memberships
   * in the space—are also deleted. For an example, see [Delete a
   * space](https://developers.google.com/chat/api/guides/v1/spaces/delete).
   * Requires [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users)
   * from a user who has permission to delete the space. (spaces.delete)
   *
   * @param string $name Required. Resource name of the space to delete. Format:
   * `spaces/{space}`
   * @param array $optParams Optional parameters.
   * @return ChatEmpty
   * @throws \Google\Service\Exception
   */
  public function delete($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('delete', [$params], ChatEmpty::class);
  }
  /**
   * Returns the existing direct message with the specified user. If no direct
   * message space is found, returns a `404 NOT_FOUND` error. For an example, see
   * [Find a direct message](/chat/api/guides/v1/spaces/find-direct-message). With
   * [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users),
   * returns the direct message space between the specified user and the
   * authenticated user. With [app
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts), returns the direct message space between the specified user and
   * the calling Chat app. Requires [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users) or
   * [app
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts). (spaces.findDirectMessage)
   *
   * @param array $optParams Optional parameters.
   *
   * @opt_param string name Required. Resource name of the user to find direct
   * message with. Format: `users/{user}`, where `{user}` is either the `id` for
   * the [person](https://developers.google.com/people/api/rest/v1/people) from
   * the People API, or the `id` for the
   * [user](https://developers.google.com/admin-
   * sdk/directory/reference/rest/v1/users) in the Directory API. For example, if
   * the People API profile ID is `123456789`, you can find a direct message with
   * that person by using `users/123456789` as the `name`. When [authenticated as
   * a user](https://developers.google.com/chat/api/guides/auth/users), you can
   * use the email as an alias for `{user}`. For example,
   * `users/example@gmail.com` where `example@gmail.com` is the email of the
   * Google Chat user.
   * @return Space
   * @throws \Google\Service\Exception
   */
  public function findDirectMessage($optParams = [])
  {
    $params = [];
    $params = array_merge($params, $optParams);
    return $this->call('findDirectMessage', [$params], Space::class);
  }
  /**
   * Returns details about a space. For an example, see [Get a
   * space](https://developers.google.com/chat/api/guides/v1/spaces/get). Requires
   * [authentication](https://developers.google.com/chat/api/guides/auth).
   * Supports [app
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts) and [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users).
   * (spaces.get)
   *
   * @param string $name Required. Resource name of the space, in the form
   * "spaces". Format: `spaces/{space}`
   * @param array $optParams Optional parameters.
   * @return Space
   * @throws \Google\Service\Exception
   */
  public function get($name, $optParams = [])
  {
    $params = ['name' => $name];
    $params = array_merge($params, $optParams);
    return $this->call('get', [$params], Space::class);
  }
  /**
   * Lists spaces the caller is a member of. Group chats and DMs aren't listed
   * until the first message is sent. For an example, see [List
   * spaces](https://developers.google.com/chat/api/guides/v1/spaces/list).
   * Requires
   * [authentication](https://developers.google.com/chat/api/guides/auth).
   * Supports [app
   * authentication](https://developers.google.com/chat/api/guides/auth/service-
   * accounts) and [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users).
   * Lists spaces visible to the caller or authenticated user. Group chats and DMs
   * aren't listed until the first message is sent. (spaces.listSpaces)
   *
   * @param array $optParams Optional parameters.
   *
   * @opt_param string filter Optional. A query filter. You can filter spaces by
   * the space type ([`space_type`](https://developers.google.com/chat/api/referen
   * ce/rest/v1/spaces#spacetype)). To filter by space type, you must specify
   * valid enum value, such as `SPACE` or `GROUP_CHAT` (the `space_type` can't be
   * `SPACE_TYPE_UNSPECIFIED`). To query for multiple space types, use the `OR`
   * operator. For example, the following queries are valid: ``` space_type =
   * "SPACE" spaceType = "GROUP_CHAT" OR spaceType = "DIRECT_MESSAGE" ``` Invalid
   * queries are rejected by the server with an `INVALID_ARGUMENT` error.
   * @opt_param int pageSize Optional. The maximum number of spaces to return. The
   * service might return fewer than this value. If unspecified, at most 100
   * spaces are returned. The maximum value is 1,000. If you use a value more than
   * 1,000, it's automatically changed to 1,000. Negative values return an
   * `INVALID_ARGUMENT` error.
   * @opt_param string pageToken Optional. A page token, received from a previous
   * list spaces call. Provide this parameter to retrieve the subsequent page.
   * When paginating, the filter value should match the call that provided the
   * page token. Passing a different value may lead to unexpected results.
   * @return ListSpacesResponse
   * @throws \Google\Service\Exception
   */
  public function listSpaces($optParams = [])
  {
    $params = [];
    $params = array_merge($params, $optParams);
    return $this->call('list', [$params], ListSpacesResponse::class);
  }
  /**
   * Updates a space. For an example, see [Update a
   * space](https://developers.google.com/chat/api/guides/v1/spaces/update). If
   * you're updating the `displayName` field and receive the error message
   * `ALREADY_EXISTS`, try a different display name.. An existing space within the
   * Google Workspace organization might already use this display name. Requires
   * [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users).
   * (spaces.patch)
   *
   * @param string $name Resource name of the space. Format: `spaces/{space}`
   * @param Space $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask Required. The updated field paths, comma
   * separated if there are multiple. Currently supported field paths: -
   * `display_name` (Only supports changing the display name of a space with the
   * `SPACE` type, or when also including the `space_type` mask to change a
   * `GROUP_CHAT` space type to `SPACE`. Trying to update the display name of a
   * `GROUP_CHAT` or a `DIRECT_MESSAGE` space results in an invalid argument
   * error. If you receive the error message `ALREADY_EXISTS` when updating the
   * `displayName`, try a different `displayName`. An existing space within the
   * Google Workspace organization might already use this display name.) -
   * `space_type` (Only supports changing a `GROUP_CHAT` space type to `SPACE`.
   * Include `display_name` together with `space_type` in the update mask and
   * ensure that the specified space has a non-empty display name and the `SPACE`
   * space type. Including the `space_type` mask and the `SPACE` type in the
   * specified space when updating the display name is optional if the existing
   * space already has the `SPACE` type. Trying to update the space type in other
   * ways results in an invalid argument error). - `space_details` -
   * `space_history_state` (Supports [turning history on or off for the
   * space](https://support.google.com/chat/answer/7664687) if [the organization
   * allows users to change their history
   * setting](https://support.google.com/a/answer/7664184). Warning: mutually
   * exclusive with all other field paths.) - Developer Preview:
   * `access_settings.audience` (Supports changing the [access
   * setting](https://support.google.com/chat/answer/11971020) of a space. If no
   * audience is specified in the access setting, the space's access setting is
   * updated to restricted. Warning: mutually exclusive with all other field
   * paths.)
   * @return Space
   * @throws \Google\Service\Exception
   */
  public function patch($name, Space $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], Space::class);
  }
  /**
   * Creates a space and adds specified users to it. The calling user is
   * automatically added to the space, and shouldn't be specified as a membership
   * in the request. For an example, see [Set up a
   * space](https://developers.google.com/chat/api/guides/v1/spaces/set-up). To
   * specify the human members to add, add memberships with the appropriate
   * `member.name` in the `SetUpSpaceRequest`. To add a human user, use
   * `users/{user}`, where `{user}` can be the email address for the user. For
   * users in the same Workspace organization `{user}` can also be the `id` for
   * the person from the People API, or the `id` for the user in the Directory
   * API. For example, if the People API Person profile ID for `user@example.com`
   * is `123456789`, you can add the user to the space by setting the
   * `membership.member.name` to `users/user@example.com` or `users/123456789`.
   * For a space or group chat, if the caller blocks or is blocked by some
   * members, then those members aren't added to the created space. To create a
   * direct message (DM) between the calling user and another human user, specify
   * exactly one membership to represent the human user. If one user blocks the
   * other, the request fails and the DM isn't created. To create a DM between the
   * calling user and the calling app, set `Space.singleUserBotDm` to `true` and
   * don't specify any memberships. You can only use this method to set up a DM
   * with the calling app. To add the calling app as a member of a space or an
   * existing DM between two human users, see [create a
   * membership](https://developers.google.com/chat/api/guides/v1/members/create).
   * If a DM already exists between two users, even when one user blocks the other
   * at the time a request is made, then the existing DM is returned. Spaces with
   * threaded replies aren't supported. If you receive the error message
   * `ALREADY_EXISTS` when setting up a space, try a different `displayName`. An
   * existing space within the Google Workspace organization might already use
   * this display name. Requires [user
   * authentication](https://developers.google.com/chat/api/guides/auth/users).
   * (spaces.setup)
   *
   * @param SetUpSpaceRequest $postBody
   * @param array $optParams Optional parameters.
   * @return Space
   * @throws \Google\Service\Exception
   */
  public function setup(SetUpSpaceRequest $postBody, $optParams = [])
  {
    $params = ['postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('setup', [$params], Space::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Spaces::class, 'Google_Service_HangoutsChat_Resource_Spaces');
