<?php
/*
 * Copyright 2016 Google Inc.
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

class Google_Service_Clouderrorreporting_ErrorContext extends Google_Model
{
  protected $httpRequestType = 'Google_Service_Clouderrorreporting_HttpRequestContext';
  protected $httpRequestDataType = '';
  protected $reportLocationType = 'Google_Service_Clouderrorreporting_SourceLocation';
  protected $reportLocationDataType = '';
  public $user;

  public function setHttpRequest(Google_Service_Clouderrorreporting_HttpRequestContext $httpRequest)
  {
    $this->httpRequest = $httpRequest;
  }
  public function getHttpRequest()
  {
    return $this->httpRequest;
  }
  public function setReportLocation(Google_Service_Clouderrorreporting_SourceLocation $reportLocation)
  {
    $this->reportLocation = $reportLocation;
  }
  public function getReportLocation()
  {
    return $this->reportLocation;
  }
  public function setUser($user)
  {
    $this->user = $user;
  }
  public function getUser()
  {
    return $this->user;
  }
}
