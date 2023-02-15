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

class NlpSemanticParsingLocalLocationConstraint extends \Google\Model
{
  protected $amenitiesType = NlpSemanticParsingLocalAmenities::class;
  protected $amenitiesDataType = '';
  public $amenities;
  protected $chainMemberType = NlpSemanticParsingLocalChainMemberConstraint::class;
  protected $chainMemberDataType = '';
  public $chainMember;
  protected $cuisineType = NlpSemanticParsingLocalCuisineConstraint::class;
  protected $cuisineDataType = '';
  public $cuisine;
  protected $evcsSpeedConstraintType = NlpSemanticParsingLocalEvChargingStationSpeedConstraint::class;
  protected $evcsSpeedConstraintDataType = '';
  public $evcsSpeedConstraint;
  protected $gcidConstraintType = NlpSemanticParsingLocalGcidConstraint::class;
  protected $gcidConstraintDataType = '';
  public $gcidConstraint;
  protected $healthInsuranceType = NlpSemanticParsingLocalHealthInsuranceConstraint::class;
  protected $healthInsuranceDataType = '';
  public $healthInsurance;
  protected $hyperReliableDataType = NlpSemanticParsingLocalHyperReliableData::class;
  protected $hyperReliableDataDataType = '';
  public $hyperReliableData;
  protected $menuItemType = NlpSemanticParsingLocalMenuItem::class;
  protected $menuItemDataType = '';
  public $menuItem;
  /**
   * @var bool
   */
  public $new;
  /**
   * @var int
   */
  public $numBytes;
  /**
   * @var bool
   */
  public $open24Hours;
  protected $priceType = NlpSemanticParsingLocalPriceConstraint::class;
  protected $priceDataType = '';
  public $price;
  protected $qualityType = NlpSemanticParsingLocalQualityConstraint::class;
  protected $qualityDataType = '';
  public $quality;
  protected $roomsType = NlpSemanticParsingLocalRoomConstraint::class;
  protected $roomsDataType = '';
  public $rooms;
  protected $scalableAttributeType = NlpSemanticParsingLocalScalableAttribute::class;
  protected $scalableAttributeDataType = '';
  public $scalableAttribute;
  protected $serviceType = NlpSemanticParsingLocalServiceConstraint::class;
  protected $serviceDataType = '';
  public $service;
  /**
   * @var int
   */
  public $startByte;
  /**
   * @var string
   */
  public $text;
  /**
   * @var bool
   */
  public $ungroundedConstraint;
  /**
   * @var bool
   */
  public $unspecified;
  /**
   * @var string
   */
  public $vaccineType;
  protected $visitHistoryType = NlpSemanticParsingLocalVisitHistoryConstraint::class;
  protected $visitHistoryDataType = '';
  public $visitHistory;

  /**
   * @param NlpSemanticParsingLocalAmenities
   */
  public function setAmenities(NlpSemanticParsingLocalAmenities $amenities)
  {
    $this->amenities = $amenities;
  }
  /**
   * @return NlpSemanticParsingLocalAmenities
   */
  public function getAmenities()
  {
    return $this->amenities;
  }
  /**
   * @param NlpSemanticParsingLocalChainMemberConstraint
   */
  public function setChainMember(NlpSemanticParsingLocalChainMemberConstraint $chainMember)
  {
    $this->chainMember = $chainMember;
  }
  /**
   * @return NlpSemanticParsingLocalChainMemberConstraint
   */
  public function getChainMember()
  {
    return $this->chainMember;
  }
  /**
   * @param NlpSemanticParsingLocalCuisineConstraint
   */
  public function setCuisine(NlpSemanticParsingLocalCuisineConstraint $cuisine)
  {
    $this->cuisine = $cuisine;
  }
  /**
   * @return NlpSemanticParsingLocalCuisineConstraint
   */
  public function getCuisine()
  {
    return $this->cuisine;
  }
  /**
   * @param NlpSemanticParsingLocalEvChargingStationSpeedConstraint
   */
  public function setEvcsSpeedConstraint(NlpSemanticParsingLocalEvChargingStationSpeedConstraint $evcsSpeedConstraint)
  {
    $this->evcsSpeedConstraint = $evcsSpeedConstraint;
  }
  /**
   * @return NlpSemanticParsingLocalEvChargingStationSpeedConstraint
   */
  public function getEvcsSpeedConstraint()
  {
    return $this->evcsSpeedConstraint;
  }
  /**
   * @param NlpSemanticParsingLocalGcidConstraint
   */
  public function setGcidConstraint(NlpSemanticParsingLocalGcidConstraint $gcidConstraint)
  {
    $this->gcidConstraint = $gcidConstraint;
  }
  /**
   * @return NlpSemanticParsingLocalGcidConstraint
   */
  public function getGcidConstraint()
  {
    return $this->gcidConstraint;
  }
  /**
   * @param NlpSemanticParsingLocalHealthInsuranceConstraint
   */
  public function setHealthInsurance(NlpSemanticParsingLocalHealthInsuranceConstraint $healthInsurance)
  {
    $this->healthInsurance = $healthInsurance;
  }
  /**
   * @return NlpSemanticParsingLocalHealthInsuranceConstraint
   */
  public function getHealthInsurance()
  {
    return $this->healthInsurance;
  }
  /**
   * @param NlpSemanticParsingLocalHyperReliableData
   */
  public function setHyperReliableData(NlpSemanticParsingLocalHyperReliableData $hyperReliableData)
  {
    $this->hyperReliableData = $hyperReliableData;
  }
  /**
   * @return NlpSemanticParsingLocalHyperReliableData
   */
  public function getHyperReliableData()
  {
    return $this->hyperReliableData;
  }
  /**
   * @param NlpSemanticParsingLocalMenuItem
   */
  public function setMenuItem(NlpSemanticParsingLocalMenuItem $menuItem)
  {
    $this->menuItem = $menuItem;
  }
  /**
   * @return NlpSemanticParsingLocalMenuItem
   */
  public function getMenuItem()
  {
    return $this->menuItem;
  }
  /**
   * @param bool
   */
  public function setNew($new)
  {
    $this->new = $new;
  }
  /**
   * @return bool
   */
  public function getNew()
  {
    return $this->new;
  }
  /**
   * @param int
   */
  public function setNumBytes($numBytes)
  {
    $this->numBytes = $numBytes;
  }
  /**
   * @return int
   */
  public function getNumBytes()
  {
    return $this->numBytes;
  }
  /**
   * @param bool
   */
  public function setOpen24Hours($open24Hours)
  {
    $this->open24Hours = $open24Hours;
  }
  /**
   * @return bool
   */
  public function getOpen24Hours()
  {
    return $this->open24Hours;
  }
  /**
   * @param NlpSemanticParsingLocalPriceConstraint
   */
  public function setPrice(NlpSemanticParsingLocalPriceConstraint $price)
  {
    $this->price = $price;
  }
  /**
   * @return NlpSemanticParsingLocalPriceConstraint
   */
  public function getPrice()
  {
    return $this->price;
  }
  /**
   * @param NlpSemanticParsingLocalQualityConstraint
   */
  public function setQuality(NlpSemanticParsingLocalQualityConstraint $quality)
  {
    $this->quality = $quality;
  }
  /**
   * @return NlpSemanticParsingLocalQualityConstraint
   */
  public function getQuality()
  {
    return $this->quality;
  }
  /**
   * @param NlpSemanticParsingLocalRoomConstraint
   */
  public function setRooms(NlpSemanticParsingLocalRoomConstraint $rooms)
  {
    $this->rooms = $rooms;
  }
  /**
   * @return NlpSemanticParsingLocalRoomConstraint
   */
  public function getRooms()
  {
    return $this->rooms;
  }
  /**
   * @param NlpSemanticParsingLocalScalableAttribute
   */
  public function setScalableAttribute(NlpSemanticParsingLocalScalableAttribute $scalableAttribute)
  {
    $this->scalableAttribute = $scalableAttribute;
  }
  /**
   * @return NlpSemanticParsingLocalScalableAttribute
   */
  public function getScalableAttribute()
  {
    return $this->scalableAttribute;
  }
  /**
   * @param NlpSemanticParsingLocalServiceConstraint
   */
  public function setService(NlpSemanticParsingLocalServiceConstraint $service)
  {
    $this->service = $service;
  }
  /**
   * @return NlpSemanticParsingLocalServiceConstraint
   */
  public function getService()
  {
    return $this->service;
  }
  /**
   * @param int
   */
  public function setStartByte($startByte)
  {
    $this->startByte = $startByte;
  }
  /**
   * @return int
   */
  public function getStartByte()
  {
    return $this->startByte;
  }
  /**
   * @param string
   */
  public function setText($text)
  {
    $this->text = $text;
  }
  /**
   * @return string
   */
  public function getText()
  {
    return $this->text;
  }
  /**
   * @param bool
   */
  public function setUngroundedConstraint($ungroundedConstraint)
  {
    $this->ungroundedConstraint = $ungroundedConstraint;
  }
  /**
   * @return bool
   */
  public function getUngroundedConstraint()
  {
    return $this->ungroundedConstraint;
  }
  /**
   * @param bool
   */
  public function setUnspecified($unspecified)
  {
    $this->unspecified = $unspecified;
  }
  /**
   * @return bool
   */
  public function getUnspecified()
  {
    return $this->unspecified;
  }
  /**
   * @param string
   */
  public function setVaccineType($vaccineType)
  {
    $this->vaccineType = $vaccineType;
  }
  /**
   * @return string
   */
  public function getVaccineType()
  {
    return $this->vaccineType;
  }
  /**
   * @param NlpSemanticParsingLocalVisitHistoryConstraint
   */
  public function setVisitHistory(NlpSemanticParsingLocalVisitHistoryConstraint $visitHistory)
  {
    $this->visitHistory = $visitHistory;
  }
  /**
   * @return NlpSemanticParsingLocalVisitHistoryConstraint
   */
  public function getVisitHistory()
  {
    return $this->visitHistory;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(NlpSemanticParsingLocalLocationConstraint::class, 'Google_Service_Contentwarehouse_NlpSemanticParsingLocalLocationConstraint');
