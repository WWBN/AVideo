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

class GoogleChromeManagementV1TelemetryUserDevice extends \Google\Collection
{
  protected $collection_key = 'peripheralsReport';
  protected $audioStatusReportType = GoogleChromeManagementV1AudioStatusReport::class;
  protected $audioStatusReportDataType = 'array';
  public $audioStatusReport;
  /**
   * @var string
   */
  public $deviceId;
  protected $peripheralsReportType = GoogleChromeManagementV1PeripheralsReport::class;
  protected $peripheralsReportDataType = 'array';
  public $peripheralsReport;

  /**
   * @param GoogleChromeManagementV1AudioStatusReport[]
   */
  public function setAudioStatusReport($audioStatusReport)
  {
    $this->audioStatusReport = $audioStatusReport;
  }
  /**
   * @return GoogleChromeManagementV1AudioStatusReport[]
   */
  public function getAudioStatusReport()
  {
    return $this->audioStatusReport;
  }
  /**
   * @param string
   */
  public function setDeviceId($deviceId)
  {
    $this->deviceId = $deviceId;
  }
  /**
   * @return string
   */
  public function getDeviceId()
  {
    return $this->deviceId;
  }
  /**
   * @param GoogleChromeManagementV1PeripheralsReport[]
   */
  public function setPeripheralsReport($peripheralsReport)
  {
    $this->peripheralsReport = $peripheralsReport;
  }
  /**
   * @return GoogleChromeManagementV1PeripheralsReport[]
   */
  public function getPeripheralsReport()
  {
    return $this->peripheralsReport;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleChromeManagementV1TelemetryUserDevice::class, 'Google_Service_ChromeManagement_GoogleChromeManagementV1TelemetryUserDevice');
