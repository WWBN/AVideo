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

namespace Google\Service\ChromeManagement;

class GoogleChromeManagementV1TelemetryEvent extends \Google\Model
{
  protected $audioSevereUnderrunEventType = GoogleChromeManagementV1TelemetryAudioSevereUnderrunEvent::class;
  protected $audioSevereUnderrunEventDataType = '';
  protected $deviceType = GoogleChromeManagementV1TelemetryDeviceInfo::class;
  protected $deviceDataType = '';
  /**
   * @var string
   */
  public $eventType;
  protected $httpsLatencyChangeEventType = GoogleChromeManagementV1TelemetryHttpsLatencyChangeEvent::class;
  protected $httpsLatencyChangeEventDataType = '';
  /**
   * @var string
   */
  public $name;
  protected $networkStateChangeEventType = GoogleChromeManagementV1TelemetryNetworkConnectionStateChangeEvent::class;
  protected $networkStateChangeEventDataType = '';
  /**
   * @var string
   */
  public $reportTime;
  protected $usbPeripheralsEventType = GoogleChromeManagementV1TelemetryUsbPeripheralsEvent::class;
  protected $usbPeripheralsEventDataType = '';
  protected $userType = GoogleChromeManagementV1TelemetryUserInfo::class;
  protected $userDataType = '';
  protected $vpnConnectionStateChangeEventType = GoogleChromeManagementV1TelemetryNetworkConnectionStateChangeEvent::class;
  protected $vpnConnectionStateChangeEventDataType = '';
  protected $wifiSignalStrengthEventType = GoogleChromeManagementV1TelemetryNetworkSignalStrengthEvent::class;
  protected $wifiSignalStrengthEventDataType = '';

  /**
   * @param GoogleChromeManagementV1TelemetryAudioSevereUnderrunEvent
   */
  public function setAudioSevereUnderrunEvent(GoogleChromeManagementV1TelemetryAudioSevereUnderrunEvent $audioSevereUnderrunEvent)
  {
    $this->audioSevereUnderrunEvent = $audioSevereUnderrunEvent;
  }
  /**
   * @return GoogleChromeManagementV1TelemetryAudioSevereUnderrunEvent
   */
  public function getAudioSevereUnderrunEvent()
  {
    return $this->audioSevereUnderrunEvent;
  }
  /**
   * @param GoogleChromeManagementV1TelemetryDeviceInfo
   */
  public function setDevice(GoogleChromeManagementV1TelemetryDeviceInfo $device)
  {
    $this->device = $device;
  }
  /**
   * @return GoogleChromeManagementV1TelemetryDeviceInfo
   */
  public function getDevice()
  {
    return $this->device;
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
   * @param GoogleChromeManagementV1TelemetryHttpsLatencyChangeEvent
   */
  public function setHttpsLatencyChangeEvent(GoogleChromeManagementV1TelemetryHttpsLatencyChangeEvent $httpsLatencyChangeEvent)
  {
    $this->httpsLatencyChangeEvent = $httpsLatencyChangeEvent;
  }
  /**
   * @return GoogleChromeManagementV1TelemetryHttpsLatencyChangeEvent
   */
  public function getHttpsLatencyChangeEvent()
  {
    return $this->httpsLatencyChangeEvent;
  }
  /**
   * @param string
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param GoogleChromeManagementV1TelemetryNetworkConnectionStateChangeEvent
   */
  public function setNetworkStateChangeEvent(GoogleChromeManagementV1TelemetryNetworkConnectionStateChangeEvent $networkStateChangeEvent)
  {
    $this->networkStateChangeEvent = $networkStateChangeEvent;
  }
  /**
   * @return GoogleChromeManagementV1TelemetryNetworkConnectionStateChangeEvent
   */
  public function getNetworkStateChangeEvent()
  {
    return $this->networkStateChangeEvent;
  }
  /**
   * @param string
   */
  public function setReportTime($reportTime)
  {
    $this->reportTime = $reportTime;
  }
  /**
   * @return string
   */
  public function getReportTime()
  {
    return $this->reportTime;
  }
  /**
   * @param GoogleChromeManagementV1TelemetryUsbPeripheralsEvent
   */
  public function setUsbPeripheralsEvent(GoogleChromeManagementV1TelemetryUsbPeripheralsEvent $usbPeripheralsEvent)
  {
    $this->usbPeripheralsEvent = $usbPeripheralsEvent;
  }
  /**
   * @return GoogleChromeManagementV1TelemetryUsbPeripheralsEvent
   */
  public function getUsbPeripheralsEvent()
  {
    return $this->usbPeripheralsEvent;
  }
  /**
   * @param GoogleChromeManagementV1TelemetryUserInfo
   */
  public function setUser(GoogleChromeManagementV1TelemetryUserInfo $user)
  {
    $this->user = $user;
  }
  /**
   * @return GoogleChromeManagementV1TelemetryUserInfo
   */
  public function getUser()
  {
    return $this->user;
  }
  /**
   * @param GoogleChromeManagementV1TelemetryNetworkConnectionStateChangeEvent
   */
  public function setVpnConnectionStateChangeEvent(GoogleChromeManagementV1TelemetryNetworkConnectionStateChangeEvent $vpnConnectionStateChangeEvent)
  {
    $this->vpnConnectionStateChangeEvent = $vpnConnectionStateChangeEvent;
  }
  /**
   * @return GoogleChromeManagementV1TelemetryNetworkConnectionStateChangeEvent
   */
  public function getVpnConnectionStateChangeEvent()
  {
    return $this->vpnConnectionStateChangeEvent;
  }
  /**
   * @param GoogleChromeManagementV1TelemetryNetworkSignalStrengthEvent
   */
  public function setWifiSignalStrengthEvent(GoogleChromeManagementV1TelemetryNetworkSignalStrengthEvent $wifiSignalStrengthEvent)
  {
    $this->wifiSignalStrengthEvent = $wifiSignalStrengthEvent;
  }
  /**
   * @return GoogleChromeManagementV1TelemetryNetworkSignalStrengthEvent
   */
  public function getWifiSignalStrengthEvent()
  {
    return $this->wifiSignalStrengthEvent;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleChromeManagementV1TelemetryEvent::class, 'Google_Service_ChromeManagement_GoogleChromeManagementV1TelemetryEvent');
