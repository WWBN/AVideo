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

class GeostoreSlopeProto extends \Google\Model
{
  /**
   * @var float
   */
  public $slopeValue;
  /**
   * @var float
   */
  public $startPointFraction;

  /**
   * @param float
   */
  public function setSlopeValue($slopeValue)
  {
    $this->slopeValue = $slopeValue;
  }
  /**
   * @return float
   */
  public function getSlopeValue()
  {
    return $this->slopeValue;
  }
  /**
   * @param float
   */
  public function setStartPointFraction($startPointFraction)
  {
    $this->startPointFraction = $startPointFraction;
  }
  /**
   * @return float
   */
  public function getStartPointFraction()
  {
    return $this->startPointFraction;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GeostoreSlopeProto::class, 'Google_Service_Contentwarehouse_GeostoreSlopeProto');
