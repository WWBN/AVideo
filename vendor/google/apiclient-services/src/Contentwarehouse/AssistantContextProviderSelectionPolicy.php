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

namespace Google\Service\Contentwarehouse;

class AssistantContextProviderSelectionPolicy extends \Google\Model
{
  /**
   * @var string
   */
  public $policyAction;
  /**
   * @var string
   */
  public $policyName;

  /**
   * @param string
   */
  public function setPolicyAction($policyAction)
  {
    $this->policyAction = $policyAction;
  }
  /**
   * @return string
   */
  public function getPolicyAction()
  {
    return $this->policyAction;
  }
  /**
   * @param string
   */
  public function setPolicyName($policyName)
  {
    $this->policyName = $policyName;
  }
  /**
   * @return string
   */
  public function getPolicyName()
  {
    return $this->policyName;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantContextProviderSelectionPolicy::class, 'Google_Service_Contentwarehouse_AssistantContextProviderSelectionPolicy');
