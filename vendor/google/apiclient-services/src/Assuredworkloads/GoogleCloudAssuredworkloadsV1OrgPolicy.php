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

namespace Google\Service\Assuredworkloads;

class GoogleCloudAssuredworkloadsV1OrgPolicy extends \Google\Model
{
  /**
   * @var string
   */
  public $constraint;
  /**
   * @var bool
   */
  public $inherit;
  /**
   * @var bool
   */
  public $reset;
  /**
   * @var string
   */
  public $resource;
  protected $ruleType = GoogleCloudAssuredworkloadsV1OrgPolicyPolicyRule::class;
  protected $ruleDataType = '';

  /**
   * @param string
   */
  public function setConstraint($constraint)
  {
    $this->constraint = $constraint;
  }
  /**
   * @return string
   */
  public function getConstraint()
  {
    return $this->constraint;
  }
  /**
   * @param bool
   */
  public function setInherit($inherit)
  {
    $this->inherit = $inherit;
  }
  /**
   * @return bool
   */
  public function getInherit()
  {
    return $this->inherit;
  }
  /**
   * @param bool
   */
  public function setReset($reset)
  {
    $this->reset = $reset;
  }
  /**
   * @return bool
   */
  public function getReset()
  {
    return $this->reset;
  }
  /**
   * @param string
   */
  public function setResource($resource)
  {
    $this->resource = $resource;
  }
  /**
   * @return string
   */
  public function getResource()
  {
    return $this->resource;
  }
  /**
   * @param GoogleCloudAssuredworkloadsV1OrgPolicyPolicyRule
   */
  public function setRule(GoogleCloudAssuredworkloadsV1OrgPolicyPolicyRule $rule)
  {
    $this->rule = $rule;
  }
  /**
   * @return GoogleCloudAssuredworkloadsV1OrgPolicyPolicyRule
   */
  public function getRule()
  {
    return $this->rule;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudAssuredworkloadsV1OrgPolicy::class, 'Google_Service_Assuredworkloads_GoogleCloudAssuredworkloadsV1OrgPolicy');
