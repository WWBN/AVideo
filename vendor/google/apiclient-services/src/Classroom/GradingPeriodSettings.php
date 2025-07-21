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

namespace Google\Service\Classroom;

class GradingPeriodSettings extends \Google\Collection
{
  protected $collection_key = 'gradingPeriods';
  /**
   * @var bool
   */
  public $applyToExistingCoursework;
  protected $gradingPeriodsType = GradingPeriod::class;
  protected $gradingPeriodsDataType = 'array';

  /**
   * @param bool
   */
  public function setApplyToExistingCoursework($applyToExistingCoursework)
  {
    $this->applyToExistingCoursework = $applyToExistingCoursework;
  }
  /**
   * @return bool
   */
  public function getApplyToExistingCoursework()
  {
    return $this->applyToExistingCoursework;
  }
  /**
   * @param GradingPeriod[]
   */
  public function setGradingPeriods($gradingPeriods)
  {
    $this->gradingPeriods = $gradingPeriods;
  }
  /**
   * @return GradingPeriod[]
   */
  public function getGradingPeriods()
  {
    return $this->gradingPeriods;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GradingPeriodSettings::class, 'Google_Service_Classroom_GradingPeriodSettings');
