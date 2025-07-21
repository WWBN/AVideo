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

namespace Google\Service\Compute;

class BackendServiceHAPolicy extends \Google\Model
{
  /**
   * @var string
   */
  public $fastIPMove;
  protected $leaderType = BackendServiceHAPolicyLeader::class;
  protected $leaderDataType = '';

  /**
   * @param string
   */
  public function setFastIPMove($fastIPMove)
  {
    $this->fastIPMove = $fastIPMove;
  }
  /**
   * @return string
   */
  public function getFastIPMove()
  {
    return $this->fastIPMove;
  }
  /**
   * @param BackendServiceHAPolicyLeader
   */
  public function setLeader(BackendServiceHAPolicyLeader $leader)
  {
    $this->leader = $leader;
  }
  /**
   * @return BackendServiceHAPolicyLeader
   */
  public function getLeader()
  {
    return $this->leader;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(BackendServiceHAPolicy::class, 'Google_Service_Compute_BackendServiceHAPolicy');
