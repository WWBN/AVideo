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

namespace Google\Service\HangoutsChat;

class SpaceNotificationSetting extends \Google\Model
{
  /**
   * @var string
   */
  public $muteSetting;
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $notificationSetting;

  /**
   * @param string
   */
  public function setMuteSetting($muteSetting)
  {
    $this->muteSetting = $muteSetting;
  }
  /**
   * @return string
   */
  public function getMuteSetting()
  {
    return $this->muteSetting;
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
  /**
   * @param string
   */
  public function setNotificationSetting($notificationSetting)
  {
    $this->notificationSetting = $notificationSetting;
  }
  /**
   * @return string
   */
  public function getNotificationSetting()
  {
    return $this->notificationSetting;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SpaceNotificationSetting::class, 'Google_Service_HangoutsChat_SpaceNotificationSetting');
