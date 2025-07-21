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

class PointsDetails extends \Google\Model
{
  protected $pointsCouponValueType = Money::class;
  protected $pointsCouponValueDataType = '';
  /**
   * @var string
   */
  public $pointsDiscountRateMicros;
  /**
   * @var string
   */
  public $pointsOfferId;
  /**
   * @var string
   */
  public $pointsSpent;

  /**
   * @param Money
   */
  public function setPointsCouponValue(Money $pointsCouponValue)
  {
    $this->pointsCouponValue = $pointsCouponValue;
  }
  /**
   * @return Money
   */
  public function getPointsCouponValue()
  {
    return $this->pointsCouponValue;
  }
  /**
   * @param string
   */
  public function setPointsDiscountRateMicros($pointsDiscountRateMicros)
  {
    $this->pointsDiscountRateMicros = $pointsDiscountRateMicros;
  }
  /**
   * @return string
   */
  public function getPointsDiscountRateMicros()
  {
    return $this->pointsDiscountRateMicros;
  }
  /**
   * @param string
   */
  public function setPointsOfferId($pointsOfferId)
  {
    $this->pointsOfferId = $pointsOfferId;
  }
  /**
   * @return string
   */
  public function getPointsOfferId()
  {
    return $this->pointsOfferId;
  }
  /**
   * @param string
   */
  public function setPointsSpent($pointsSpent)
  {
    $this->pointsSpent = $pointsSpent;
  }
  /**
   * @return string
   */
  public function getPointsSpent()
  {
    return $this->pointsSpent;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PointsDetails::class, 'Google_Service_AndroidPublisher_PointsDetails');
