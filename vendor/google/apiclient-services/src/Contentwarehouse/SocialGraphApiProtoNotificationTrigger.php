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

class SocialGraphApiProtoNotificationTrigger extends \Google\Model
{
  /**
   * @var int
   */
  public $daysBeforeActiveDate;
  protected $notificationTimeOfDayType = GoogleTypeTimeOfDay::class;
  protected $notificationTimeOfDayDataType = '';

  /**
   * @param int
   */
  public function setDaysBeforeActiveDate($daysBeforeActiveDate)
  {
    $this->daysBeforeActiveDate = $daysBeforeActiveDate;
  }
  /**
   * @return int
   */
  public function getDaysBeforeActiveDate()
  {
    return $this->daysBeforeActiveDate;
  }
  /**
   * @param GoogleTypeTimeOfDay
   */
  public function setNotificationTimeOfDay(GoogleTypeTimeOfDay $notificationTimeOfDay)
  {
    $this->notificationTimeOfDay = $notificationTimeOfDay;
  }
  /**
   * @return GoogleTypeTimeOfDay
   */
  public function getNotificationTimeOfDay()
  {
    return $this->notificationTimeOfDay;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SocialGraphApiProtoNotificationTrigger::class, 'Google_Service_Contentwarehouse_SocialGraphApiProtoNotificationTrigger');
