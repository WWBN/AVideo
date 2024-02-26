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

class QualityCalypsoAppsUniversalAuLiveOpDetail extends \Google\Model
{
  protected $countryLevelScheduleInformationType = QualityCalypsoAppsUniversalAuLiveOpEvent::class;
  protected $countryLevelScheduleInformationDataType = 'map';
  protected $defaultFormatInformationType = QualityCalypsoAppsUniversalAuLiveOpFormat::class;
  protected $defaultFormatInformationDataType = '';
  protected $defaultScheduleInformationType = QualityCalypsoAppsUniversalAuLiveOpEvent::class;
  protected $defaultScheduleInformationDataType = '';
  /**
   * @var string
   */
  public $eventId;
  /**
   * @var string
   */
  public $eventType;
  /**
   * @var string
   */
  public $eventUrl;
  protected $localeLevelFormatInformationType = QualityCalypsoAppsUniversalAuLiveOpFormat::class;
  protected $localeLevelFormatInformationDataType = 'map';
  /**
   * @var string
   */
  public $priority;

  /**
   * @param QualityCalypsoAppsUniversalAuLiveOpEvent[]
   */
  public function setCountryLevelScheduleInformation($countryLevelScheduleInformation)
  {
    $this->countryLevelScheduleInformation = $countryLevelScheduleInformation;
  }
  /**
   * @return QualityCalypsoAppsUniversalAuLiveOpEvent[]
   */
  public function getCountryLevelScheduleInformation()
  {
    return $this->countryLevelScheduleInformation;
  }
  /**
   * @param QualityCalypsoAppsUniversalAuLiveOpFormat
   */
  public function setDefaultFormatInformation(QualityCalypsoAppsUniversalAuLiveOpFormat $defaultFormatInformation)
  {
    $this->defaultFormatInformation = $defaultFormatInformation;
  }
  /**
   * @return QualityCalypsoAppsUniversalAuLiveOpFormat
   */
  public function getDefaultFormatInformation()
  {
    return $this->defaultFormatInformation;
  }
  /**
   * @param QualityCalypsoAppsUniversalAuLiveOpEvent
   */
  public function setDefaultScheduleInformation(QualityCalypsoAppsUniversalAuLiveOpEvent $defaultScheduleInformation)
  {
    $this->defaultScheduleInformation = $defaultScheduleInformation;
  }
  /**
   * @return QualityCalypsoAppsUniversalAuLiveOpEvent
   */
  public function getDefaultScheduleInformation()
  {
    return $this->defaultScheduleInformation;
  }
  /**
   * @param string
   */
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  /**
   * @return string
   */
  public function getEventId()
  {
    return $this->eventId;
  }
  /**
   * @param string
   */
  public function setEventType($eventType)
  {
    $this->eventType = $eventType;
  }
  /**
   * @return string
   */
  public function getEventType()
  {
    return $this->eventType;
  }
  /**
   * @param string
   */
  public function setEventUrl($eventUrl)
  {
    $this->eventUrl = $eventUrl;
  }
  /**
   * @return string
   */
  public function getEventUrl()
  {
    return $this->eventUrl;
  }
  /**
   * @param QualityCalypsoAppsUniversalAuLiveOpFormat[]
   */
  public function setLocaleLevelFormatInformation($localeLevelFormatInformation)
  {
    $this->localeLevelFormatInformation = $localeLevelFormatInformation;
  }
  /**
   * @return QualityCalypsoAppsUniversalAuLiveOpFormat[]
   */
  public function getLocaleLevelFormatInformation()
  {
    return $this->localeLevelFormatInformation;
  }
  /**
   * @param string
   */
  public function setPriority($priority)
  {
    $this->priority = $priority;
  }
  /**
   * @return string
   */
  public function getPriority()
  {
    return $this->priority;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(QualityCalypsoAppsUniversalAuLiveOpDetail::class, 'Google_Service_Contentwarehouse_QualityCalypsoAppsUniversalAuLiveOpDetail');
