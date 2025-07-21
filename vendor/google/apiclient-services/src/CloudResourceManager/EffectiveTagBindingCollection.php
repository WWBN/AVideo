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

namespace Google\Service\CloudResourceManager;

class EffectiveTagBindingCollection extends \Google\Model
{
  /**
   * @var string[]
   */
  public $effectiveTags;
  /**
   * @var string
   */
  public $fullResourceName;
  /**
   * @var string
   */
  public $name;

  /**
   * @param string[]
   */
  public function setEffectiveTags($effectiveTags)
  {
    $this->effectiveTags = $effectiveTags;
  }
  /**
   * @return string[]
   */
  public function getEffectiveTags()
  {
    return $this->effectiveTags;
  }
  /**
   * @param string
   */
  public function setFullResourceName($fullResourceName)
  {
    $this->fullResourceName = $fullResourceName;
  }
  /**
   * @return string
   */
  public function getFullResourceName()
  {
    return $this->fullResourceName;
  }
  /**
   * @param string
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(EffectiveTagBindingCollection::class, 'Google_Service_CloudResourceManager_EffectiveTagBindingCollection');
