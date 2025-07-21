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

namespace Google\Service\AndroidPublisher;

class SubscriptionDetails extends \Google\Model
{
  /**
   * @var string
   */
  public $basePlanId;
  /**
   * @var string
   */
  public $offerId;
  /**
   * @var string
   */
  public $offerPhase;
  /**
   * @var string
   */
  public $servicePeriodEndTime;
  /**
   * @var string
   */
  public $servicePeriodStartTime;

  /**
   * @param string
   */
  public function setBasePlanId($basePlanId)
  {
    $this->basePlanId = $basePlanId;
  }
  /**
   * @return string
   */
  public function getBasePlanId()
  {
    return $this->basePlanId;
  }
  /**
   * @param string
   */
  public function setOfferId($offerId)
  {
    $this->offerId = $offerId;
  }
  /**
   * @return string
   */
  public function getOfferId()
  {
    return $this->offerId;
  }
  /**
   * @param string
   */
  public function setOfferPhase($offerPhase)
  {
    $this->offerPhase = $offerPhase;
  }
  /**
   * @return string
   */
  public function getOfferPhase()
  {
    return $this->offerPhase;
  }
  /**
   * @param string
   */
  public function setServicePeriodEndTime($servicePeriodEndTime)
  {
    $this->servicePeriodEndTime = $servicePeriodEndTime;
  }
  /**
   * @return string
   */
  public function getServicePeriodEndTime()
  {
    return $this->servicePeriodEndTime;
  }
  /**
   * @param string
   */
  public function setServicePeriodStartTime($servicePeriodStartTime)
  {
    $this->servicePeriodStartTime = $servicePeriodStartTime;
  }
  /**
   * @return string
   */
  public function getServicePeriodStartTime()
  {
    return $this->servicePeriodStartTime;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SubscriptionDetails::class, 'Google_Service_AndroidPublisher_SubscriptionDetails');
