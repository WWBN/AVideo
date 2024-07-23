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

namespace Google\Service\Meet\Resource;

use Google\Service\Meet\EndActiveConferenceRequest;
use Google\Service\Meet\MeetEmpty;
use Google\Service\Meet\Space;

/**
 * The "spaces" collection of methods.
 * Typical usage is:
 *  <code>
 *   $meetService = new Google\Service\Meet(...);
 *   $spaces = $meetService->spaces;
 *  </code>
 */
class Spaces extends \Google\Service\Resource
{
  /**
   * Creates a space. (spaces.create)
   *
   * @param Space $postBody
   * @param array $optParams Optional parameters.
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
   * Ends an active conference (if there's one). (spaces.endActiveConference)
   *
   * @param string $name Required. Resource name of the space.
   * @param EndActiveConferenceRequest $postBody
   * @param array $optParams Optional parameters.
   * @return MeetEmpty
   * @throws \Google\Service\Exception
   */
  public function endActiveConference($name, EndActiveConferenceRequest $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('endActiveConference', [$params], MeetEmpty::class);
  }
  /**
   * Gets a space by `space_id` or `meeting_code`. (spaces.get)
   *
   * @param string $name Required. Resource name of the space.
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
   * Updates a space. (spaces.patch)
   *
   * @param string $name Immutable. Resource name of the space. Format:
   * `spaces/{space}`
   * @param Space $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string updateMask Optional. Field mask used to specify the fields
   * to be updated in the space. If update_mask isn't provided, it defaults to '*'
   * and updates all fields provided in the request, including deleting fields not
   * set in the request.
   * @return Space
   * @throws \Google\Service\Exception
   */
  public function patch($name, Space $postBody, $optParams = [])
  {
    $params = ['name' => $name, 'postBody' => $postBody];
    $params = array_merge($params, $optParams);
    return $this->call('patch', [$params], Space::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Spaces::class, 'Google_Service_Meet_Resource_Spaces');
