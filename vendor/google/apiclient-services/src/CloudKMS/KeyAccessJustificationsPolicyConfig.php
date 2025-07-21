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

namespace Google\Service\CloudKMS;

class KeyAccessJustificationsPolicyConfig extends \Google\Model
{
  protected $defaultKeyAccessJustificationPolicyType = KeyAccessJustificationsPolicy::class;
  protected $defaultKeyAccessJustificationPolicyDataType = '';
  /**
   * @var string
   */
  public $name;

  /**
   * @param KeyAccessJustificationsPolicy
   */
  public function setDefaultKeyAccessJustificationPolicy(KeyAccessJustificationsPolicy $defaultKeyAccessJustificationPolicy)
  {
    $this->defaultKeyAccessJustificationPolicy = $defaultKeyAccessJustificationPolicy;
  }
  /**
   * @return KeyAccessJustificationsPolicy
   */
  public function getDefaultKeyAccessJustificationPolicy()
  {
    return $this->defaultKeyAccessJustificationPolicy;
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
class_alias(KeyAccessJustificationsPolicyConfig::class, 'Google_Service_CloudKMS_KeyAccessJustificationsPolicyConfig');
