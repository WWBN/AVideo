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

class SocialGraphApiProtoRecurrence extends \Google\Model
{
  protected $dailyRecurrenceType = SocialGraphApiProtoDailyRecurrence::class;
  protected $dailyRecurrenceDataType = '';
  /**
   * @var int
   */
  public $every;
  protected $monthlyRecurrenceType = SocialGraphApiProtoMonthlyRecurrence::class;
  protected $monthlyRecurrenceDataType = '';
  protected $recurrenceEndDateType = GoogleTypeDateTime::class;
  protected $recurrenceEndDateDataType = '';
  protected $recurrenceStartType = GoogleTypeDateTime::class;
  protected $recurrenceStartDataType = '';
  /**
   * @var int
   */
  public $repeatCount;
  protected $repeatForeverType = SocialGraphApiProtoRecurrenceRepeatForever::class;
  protected $repeatForeverDataType = '';
  protected $singleRecurrenceType = SocialGraphApiProtoSingleRecurrence::class;
  protected $singleRecurrenceDataType = '';
  protected $weeklyRecurrenceType = SocialGraphApiProtoWeeklyRecurrence::class;
  protected $weeklyRecurrenceDataType = '';
  protected $yearlyRecurrenceType = SocialGraphApiProtoYearlyRecurrence::class;
  protected $yearlyRecurrenceDataType = '';

  /**
   * @param SocialGraphApiProtoDailyRecurrence
   */
  public function setDailyRecurrence(SocialGraphApiProtoDailyRecurrence $dailyRecurrence)
  {
    $this->dailyRecurrence = $dailyRecurrence;
  }
  /**
   * @return SocialGraphApiProtoDailyRecurrence
   */
  public function getDailyRecurrence()
  {
    return $this->dailyRecurrence;
  }
  /**
   * @param int
   */
  public function setEvery($every)
  {
    $this->every = $every;
  }
  /**
   * @return int
   */
  public function getEvery()
  {
    return $this->every;
  }
  /**
   * @param SocialGraphApiProtoMonthlyRecurrence
   */
  public function setMonthlyRecurrence(SocialGraphApiProtoMonthlyRecurrence $monthlyRecurrence)
  {
    $this->monthlyRecurrence = $monthlyRecurrence;
  }
  /**
   * @return SocialGraphApiProtoMonthlyRecurrence
   */
  public function getMonthlyRecurrence()
  {
    return $this->monthlyRecurrence;
  }
  /**
   * @param GoogleTypeDateTime
   */
  public function setRecurrenceEndDate(GoogleTypeDateTime $recurrenceEndDate)
  {
    $this->recurrenceEndDate = $recurrenceEndDate;
  }
  /**
   * @return GoogleTypeDateTime
   */
  public function getRecurrenceEndDate()
  {
    return $this->recurrenceEndDate;
  }
  /**
   * @param GoogleTypeDateTime
   */
  public function setRecurrenceStart(GoogleTypeDateTime $recurrenceStart)
  {
    $this->recurrenceStart = $recurrenceStart;
  }
  /**
   * @return GoogleTypeDateTime
   */
  public function getRecurrenceStart()
  {
    return $this->recurrenceStart;
  }
  /**
   * @param int
   */
  public function setRepeatCount($repeatCount)
  {
    $this->repeatCount = $repeatCount;
  }
  /**
   * @return int
   */
  public function getRepeatCount()
  {
    return $this->repeatCount;
  }
  /**
   * @param SocialGraphApiProtoRecurrenceRepeatForever
   */
  public function setRepeatForever(SocialGraphApiProtoRecurrenceRepeatForever $repeatForever)
  {
    $this->repeatForever = $repeatForever;
  }
  /**
   * @return SocialGraphApiProtoRecurrenceRepeatForever
   */
  public function getRepeatForever()
  {
    return $this->repeatForever;
  }
  /**
   * @param SocialGraphApiProtoSingleRecurrence
   */
  public function setSingleRecurrence(SocialGraphApiProtoSingleRecurrence $singleRecurrence)
  {
    $this->singleRecurrence = $singleRecurrence;
  }
  /**
   * @return SocialGraphApiProtoSingleRecurrence
   */
  public function getSingleRecurrence()
  {
    return $this->singleRecurrence;
  }
  /**
   * @param SocialGraphApiProtoWeeklyRecurrence
   */
  public function setWeeklyRecurrence(SocialGraphApiProtoWeeklyRecurrence $weeklyRecurrence)
  {
    $this->weeklyRecurrence = $weeklyRecurrence;
  }
  /**
   * @return SocialGraphApiProtoWeeklyRecurrence
   */
  public function getWeeklyRecurrence()
  {
    return $this->weeklyRecurrence;
  }
  /**
   * @param SocialGraphApiProtoYearlyRecurrence
   */
  public function setYearlyRecurrence(SocialGraphApiProtoYearlyRecurrence $yearlyRecurrence)
  {
    $this->yearlyRecurrence = $yearlyRecurrence;
  }
  /**
   * @return SocialGraphApiProtoYearlyRecurrence
   */
  public function getYearlyRecurrence()
  {
    return $this->yearlyRecurrence;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SocialGraphApiProtoRecurrence::class, 'Google_Service_Contentwarehouse_SocialGraphApiProtoRecurrence');
