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

class AssistantApiCoreTypesAndroidAppInfoActivityInfoActivity extends \Google\Model
{
  /**
   * @var string
   */
  public $localizedActivityName;
  /**
   * @var string
   */
  public $shortClassName;

  /**
   * @param string
   */
  public function setLocalizedActivityName($localizedActivityName)
  {
    $this->localizedActivityName = $localizedActivityName;
  }
  /**
   * @return string
   */
  public function getLocalizedActivityName()
  {
    return $this->localizedActivityName;
  }
  /**
   * @param string
   */
  public function setShortClassName($shortClassName)
  {
    $this->shortClassName = $shortClassName;
  }
  /**
   * @return string
   */
  public function getShortClassName()
  {
    return $this->shortClassName;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantApiCoreTypesAndroidAppInfoActivityInfoActivity::class, 'Google_Service_Contentwarehouse_AssistantApiCoreTypesAndroidAppInfoActivityInfoActivity');
