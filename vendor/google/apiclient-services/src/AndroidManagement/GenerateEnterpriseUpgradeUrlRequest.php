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

namespace Google\Service\AndroidManagement;

class GenerateEnterpriseUpgradeUrlRequest extends \Google\Collection
{
  protected $collection_key = 'allowedDomains';
  /**
   * @var string
   */
  public $adminEmail;
  /**
   * @var string[]
   */
  public $allowedDomains;

  /**
   * @param string
   */
  public function setAdminEmail($adminEmail)
  {
    $this->adminEmail = $adminEmail;
  }
  /**
   * @return string
   */
  public function getAdminEmail()
  {
    return $this->adminEmail;
  }
  /**
   * @param string[]
   */
  public function setAllowedDomains($allowedDomains)
  {
    $this->allowedDomains = $allowedDomains;
  }
  /**
   * @return string[]
   */
  public function getAllowedDomains()
  {
    return $this->allowedDomains;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GenerateEnterpriseUpgradeUrlRequest::class, 'Google_Service_AndroidManagement_GenerateEnterpriseUpgradeUrlRequest');
