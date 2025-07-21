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

namespace Google\Service\Meet;

class ModerationRestrictions extends \Google\Model
{
  /**
   * @var string
   */
  public $chatRestriction;
  /**
   * @var string
   */
  public $defaultJoinAsViewerType;
  /**
   * @var string
   */
  public $presentRestriction;
  /**
   * @var string
   */
  public $reactionRestriction;

  /**
   * @param string
   */
  public function setChatRestriction($chatRestriction)
  {
    $this->chatRestriction = $chatRestriction;
  }
  /**
   * @return string
   */
  public function getChatRestriction()
  {
    return $this->chatRestriction;
  }
  /**
   * @param string
   */
  public function setDefaultJoinAsViewerType($defaultJoinAsViewerType)
  {
    $this->defaultJoinAsViewerType = $defaultJoinAsViewerType;
  }
  /**
   * @return string
   */
  public function getDefaultJoinAsViewerType()
  {
    return $this->defaultJoinAsViewerType;
  }
  /**
   * @param string
   */
  public function setPresentRestriction($presentRestriction)
  {
    $this->presentRestriction = $presentRestriction;
  }
  /**
   * @return string
   */
  public function getPresentRestriction()
  {
    return $this->presentRestriction;
  }
  /**
   * @param string
   */
  public function setReactionRestriction($reactionRestriction)
  {
    $this->reactionRestriction = $reactionRestriction;
  }
  /**
   * @return string
   */
  public function getReactionRestriction()
  {
    return $this->reactionRestriction;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ModerationRestrictions::class, 'Google_Service_Meet_ModerationRestrictions');
