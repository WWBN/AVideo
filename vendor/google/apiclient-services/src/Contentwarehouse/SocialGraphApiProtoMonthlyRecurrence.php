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

class SocialGraphApiProtoMonthlyRecurrence extends \Google\Model
{
  protected $monthlyDayRecurrenceType = SocialGraphApiProtoMonthlyDayRecurrence::class;
  protected $monthlyDayRecurrenceDataType = '';
  public $monthlyDayRecurrence;
  protected $monthlyWeekdayRecurrenceType = SocialGraphApiProtoMonthlyWeekdayRecurrence::class;
  protected $monthlyWeekdayRecurrenceDataType = '';
  public $monthlyWeekdayRecurrence;

  /**
   * @param SocialGraphApiProtoMonthlyDayRecurrence
   */
  public function setMonthlyDayRecurrence(SocialGraphApiProtoMonthlyDayRecurrence $monthlyDayRecurrence)
  {
    $this->monthlyDayRecurrence = $monthlyDayRecurrence;
  }
  /**
   * @return SocialGraphApiProtoMonthlyDayRecurrence
   */
  public function getMonthlyDayRecurrence()
  {
    return $this->monthlyDayRecurrence;
  }
  /**
   * @param SocialGraphApiProtoMonthlyWeekdayRecurrence
   */
  public function setMonthlyWeekdayRecurrence(SocialGraphApiProtoMonthlyWeekdayRecurrence $monthlyWeekdayRecurrence)
  {
    $this->monthlyWeekdayRecurrence = $monthlyWeekdayRecurrence;
  }
  /**
   * @return SocialGraphApiProtoMonthlyWeekdayRecurrence
   */
  public function getMonthlyWeekdayRecurrence()
  {
    return $this->monthlyWeekdayRecurrence;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SocialGraphApiProtoMonthlyRecurrence::class, 'Google_Service_Contentwarehouse_SocialGraphApiProtoMonthlyRecurrence');
