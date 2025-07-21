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

class InterconnectGroupsCreateMembers extends \Google\Collection
{
  protected $collection_key = 'interconnects';
  /**
   * @var string
   */
  public $intentMismatchBehavior;
  protected $interconnectsType = InterconnectGroupsCreateMembersInterconnectInput::class;
  protected $interconnectsDataType = 'array';
  protected $templateInterconnectType = InterconnectGroupsCreateMembersInterconnectInput::class;
  protected $templateInterconnectDataType = '';

  /**
   * @param string
   */
  public function setIntentMismatchBehavior($intentMismatchBehavior)
  {
    $this->intentMismatchBehavior = $intentMismatchBehavior;
  }
  /**
   * @return string
   */
  public function getIntentMismatchBehavior()
  {
    return $this->intentMismatchBehavior;
  }
  /**
   * @param InterconnectGroupsCreateMembersInterconnectInput[]
   */
  public function setInterconnects($interconnects)
  {
    $this->interconnects = $interconnects;
  }
  /**
   * @return InterconnectGroupsCreateMembersInterconnectInput[]
   */
  public function getInterconnects()
  {
    return $this->interconnects;
  }
  /**
   * @param InterconnectGroupsCreateMembersInterconnectInput
   */
  public function setTemplateInterconnect(InterconnectGroupsCreateMembersInterconnectInput $templateInterconnect)
  {
    $this->templateInterconnect = $templateInterconnect;
  }
  /**
   * @return InterconnectGroupsCreateMembersInterconnectInput
   */
  public function getTemplateInterconnect()
  {
    return $this->templateInterconnect;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(InterconnectGroupsCreateMembers::class, 'Google_Service_Compute_InterconnectGroupsCreateMembers');
