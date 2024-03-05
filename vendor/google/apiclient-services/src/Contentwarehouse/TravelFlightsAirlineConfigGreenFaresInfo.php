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

class TravelFlightsAirlineConfigGreenFaresInfo extends \Google\Model
{
  /**
   * @var string
   */
  public $bonusMilesProgramName;
  /**
   * @var string
   */
  public $bonusMilesQuantity;
  /**
   * @var string
   */
  public $bonusMilesQuantityType;
  /**
   * @var string
   */
  public $bonusMilesType;
  /**
   * @var string
   */
  public $contributionFraming;

  /**
   * @param string
   */
  public function setBonusMilesProgramName($bonusMilesProgramName)
  {
    $this->bonusMilesProgramName = $bonusMilesProgramName;
  }
  /**
   * @return string
   */
  public function getBonusMilesProgramName()
  {
    return $this->bonusMilesProgramName;
  }
  /**
   * @param string
   */
  public function setBonusMilesQuantity($bonusMilesQuantity)
  {
    $this->bonusMilesQuantity = $bonusMilesQuantity;
  }
  /**
   * @return string
   */
  public function getBonusMilesQuantity()
  {
    return $this->bonusMilesQuantity;
  }
  /**
   * @param string
   */
  public function setBonusMilesQuantityType($bonusMilesQuantityType)
  {
    $this->bonusMilesQuantityType = $bonusMilesQuantityType;
  }
  /**
   * @return string
   */
  public function getBonusMilesQuantityType()
  {
    return $this->bonusMilesQuantityType;
  }
  /**
   * @param string
   */
  public function setBonusMilesType($bonusMilesType)
  {
    $this->bonusMilesType = $bonusMilesType;
  }
  /**
   * @return string
   */
  public function getBonusMilesType()
  {
    return $this->bonusMilesType;
  }
  /**
   * @param string
   */
  public function setContributionFraming($contributionFraming)
  {
    $this->contributionFraming = $contributionFraming;
  }
  /**
   * @return string
   */
  public function getContributionFraming()
  {
    return $this->contributionFraming;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(TravelFlightsAirlineConfigGreenFaresInfo::class, 'Google_Service_Contentwarehouse_TravelFlightsAirlineConfigGreenFaresInfo');
