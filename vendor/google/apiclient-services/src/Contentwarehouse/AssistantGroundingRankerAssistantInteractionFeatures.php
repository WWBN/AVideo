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

class AssistantGroundingRankerAssistantInteractionFeatures extends \Google\Model
{
  /**
   * @var float
   */
  public $timeDecayed14dHalfLife;
  /**
   * @var float
   */
  public $timeDecayed1dHalfLife;
  /**
   * @var float
   */
  public $timeDecayed7dHalfLife;
  /**
   * @var float
   */
  public $timeDecayedAccepted14dHalfLife;
  /**
   * @var float
   */
  public $timeDecayedAuis14dHalfLife;
  /**
   * @var float
   */
  public $timeDecayedCanceled14dHalfLife;
  /**
   * @var float
   */
  public $timeDecayedDeclined14dHalfLife;
  /**
   * @var float
   */
  public $timeSinceLastButOneCanceledActionSecs;
  /**
   * @var float
   */
  public $timeSinceLastButOneCompletedActionSecs;
  /**
   * @var float
   */
  public $timeSinceLastButTwoCanceledActionSecs;
  /**
   * @var float
   */
  public $timeSinceLastButTwoCompletedActionSecs;
  /**
   * @var float
   */
  public $timeSinceLastCanceledActionSecs;
  /**
   * @var float
   */
  public $timeSinceLastCompletedActionSecs;

  /**
   * @param float
   */
  public function setTimeDecayed14dHalfLife($timeDecayed14dHalfLife)
  {
    $this->timeDecayed14dHalfLife = $timeDecayed14dHalfLife;
  }
  /**
   * @return float
   */
  public function getTimeDecayed14dHalfLife()
  {
    return $this->timeDecayed14dHalfLife;
  }
  /**
   * @param float
   */
  public function setTimeDecayed1dHalfLife($timeDecayed1dHalfLife)
  {
    $this->timeDecayed1dHalfLife = $timeDecayed1dHalfLife;
  }
  /**
   * @return float
   */
  public function getTimeDecayed1dHalfLife()
  {
    return $this->timeDecayed1dHalfLife;
  }
  /**
   * @param float
   */
  public function setTimeDecayed7dHalfLife($timeDecayed7dHalfLife)
  {
    $this->timeDecayed7dHalfLife = $timeDecayed7dHalfLife;
  }
  /**
   * @return float
   */
  public function getTimeDecayed7dHalfLife()
  {
    return $this->timeDecayed7dHalfLife;
  }
  /**
   * @param float
   */
  public function setTimeDecayedAccepted14dHalfLife($timeDecayedAccepted14dHalfLife)
  {
    $this->timeDecayedAccepted14dHalfLife = $timeDecayedAccepted14dHalfLife;
  }
  /**
   * @return float
   */
  public function getTimeDecayedAccepted14dHalfLife()
  {
    return $this->timeDecayedAccepted14dHalfLife;
  }
  /**
   * @param float
   */
  public function setTimeDecayedAuis14dHalfLife($timeDecayedAuis14dHalfLife)
  {
    $this->timeDecayedAuis14dHalfLife = $timeDecayedAuis14dHalfLife;
  }
  /**
   * @return float
   */
  public function getTimeDecayedAuis14dHalfLife()
  {
    return $this->timeDecayedAuis14dHalfLife;
  }
  /**
   * @param float
   */
  public function setTimeDecayedCanceled14dHalfLife($timeDecayedCanceled14dHalfLife)
  {
    $this->timeDecayedCanceled14dHalfLife = $timeDecayedCanceled14dHalfLife;
  }
  /**
   * @return float
   */
  public function getTimeDecayedCanceled14dHalfLife()
  {
    return $this->timeDecayedCanceled14dHalfLife;
  }
  /**
   * @param float
   */
  public function setTimeDecayedDeclined14dHalfLife($timeDecayedDeclined14dHalfLife)
  {
    $this->timeDecayedDeclined14dHalfLife = $timeDecayedDeclined14dHalfLife;
  }
  /**
   * @return float
   */
  public function getTimeDecayedDeclined14dHalfLife()
  {
    return $this->timeDecayedDeclined14dHalfLife;
  }
  /**
   * @param float
   */
  public function setTimeSinceLastButOneCanceledActionSecs($timeSinceLastButOneCanceledActionSecs)
  {
    $this->timeSinceLastButOneCanceledActionSecs = $timeSinceLastButOneCanceledActionSecs;
  }
  /**
   * @return float
   */
  public function getTimeSinceLastButOneCanceledActionSecs()
  {
    return $this->timeSinceLastButOneCanceledActionSecs;
  }
  /**
   * @param float
   */
  public function setTimeSinceLastButOneCompletedActionSecs($timeSinceLastButOneCompletedActionSecs)
  {
    $this->timeSinceLastButOneCompletedActionSecs = $timeSinceLastButOneCompletedActionSecs;
  }
  /**
   * @return float
   */
  public function getTimeSinceLastButOneCompletedActionSecs()
  {
    return $this->timeSinceLastButOneCompletedActionSecs;
  }
  /**
   * @param float
   */
  public function setTimeSinceLastButTwoCanceledActionSecs($timeSinceLastButTwoCanceledActionSecs)
  {
    $this->timeSinceLastButTwoCanceledActionSecs = $timeSinceLastButTwoCanceledActionSecs;
  }
  /**
   * @return float
   */
  public function getTimeSinceLastButTwoCanceledActionSecs()
  {
    return $this->timeSinceLastButTwoCanceledActionSecs;
  }
  /**
   * @param float
   */
  public function setTimeSinceLastButTwoCompletedActionSecs($timeSinceLastButTwoCompletedActionSecs)
  {
    $this->timeSinceLastButTwoCompletedActionSecs = $timeSinceLastButTwoCompletedActionSecs;
  }
  /**
   * @return float
   */
  public function getTimeSinceLastButTwoCompletedActionSecs()
  {
    return $this->timeSinceLastButTwoCompletedActionSecs;
  }
  /**
   * @param float
   */
  public function setTimeSinceLastCanceledActionSecs($timeSinceLastCanceledActionSecs)
  {
    $this->timeSinceLastCanceledActionSecs = $timeSinceLastCanceledActionSecs;
  }
  /**
   * @return float
   */
  public function getTimeSinceLastCanceledActionSecs()
  {
    return $this->timeSinceLastCanceledActionSecs;
  }
  /**
   * @param float
   */
  public function setTimeSinceLastCompletedActionSecs($timeSinceLastCompletedActionSecs)
  {
    $this->timeSinceLastCompletedActionSecs = $timeSinceLastCompletedActionSecs;
  }
  /**
   * @return float
   */
  public function getTimeSinceLastCompletedActionSecs()
  {
    return $this->timeSinceLastCompletedActionSecs;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantGroundingRankerAssistantInteractionFeatures::class, 'Google_Service_Contentwarehouse_AssistantGroundingRankerAssistantInteractionFeatures');
