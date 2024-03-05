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

namespace Google\Service\Aiplatform;

class LearningGenaiRootHarmSpiiFilter extends \Google\Model
{
  /**
   * @var bool
   */
  public $usBankRoutingMicr;
  /**
   * @var bool
   */
  public $usEmployerIdentificationNumber;
  /**
   * @var bool
   */
  public $usSocialSecurityNumber;

  /**
   * @param bool
   */
  public function setUsBankRoutingMicr($usBankRoutingMicr)
  {
    $this->usBankRoutingMicr = $usBankRoutingMicr;
  }
  /**
   * @return bool
   */
  public function getUsBankRoutingMicr()
  {
    return $this->usBankRoutingMicr;
  }
  /**
   * @param bool
   */
  public function setUsEmployerIdentificationNumber($usEmployerIdentificationNumber)
  {
    $this->usEmployerIdentificationNumber = $usEmployerIdentificationNumber;
  }
  /**
   * @return bool
   */
  public function getUsEmployerIdentificationNumber()
  {
    return $this->usEmployerIdentificationNumber;
  }
  /**
   * @param bool
   */
  public function setUsSocialSecurityNumber($usSocialSecurityNumber)
  {
    $this->usSocialSecurityNumber = $usSocialSecurityNumber;
  }
  /**
   * @return bool
   */
  public function getUsSocialSecurityNumber()
  {
    return $this->usSocialSecurityNumber;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootHarmSpiiFilter::class, 'Google_Service_Aiplatform_LearningGenaiRootHarmSpiiFilter');
