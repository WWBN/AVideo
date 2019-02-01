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

class Google_Service_Partners_LogUserEventRequest extends Google_Collection
{
  protected $collection_key = 'eventDatas';
  public $eventAction;
  public $eventCategory;
  protected $eventDatasType = 'Google_Service_Partners_EventData';
  protected $eventDatasDataType = 'array';
  public $eventScope;
  protected $leadType = 'Google_Service_Partners_Lead';
  protected $leadDataType = '';
  protected $requestMetadataType = 'Google_Service_Partners_RequestMetadata';
  protected $requestMetadataDataType = '';
  public $url;

  public function setEventAction($eventAction)
  {
    $this->eventAction = $eventAction;
  }
  public function getEventAction()
  {
    return $this->eventAction;
  }
  public function setEventCategory($eventCategory)
  {
    $this->eventCategory = $eventCategory;
  }
  public function getEventCategory()
  {
    return $this->eventCategory;
  }
  public function setEventDatas($eventDatas)
  {
    $this->eventDatas = $eventDatas;
  }
  public function getEventDatas()
  {
    return $this->eventDatas;
  }
  public function setEventScope($eventScope)
  {
    $this->eventScope = $eventScope;
  }
  public function getEventScope()
  {
    return $this->eventScope;
  }
  public function setLead(Google_Service_Partners_Lead $lead)
  {
    $this->lead = $lead;
  }
  public function getLead()
  {
    return $this->lead;
  }
  public function setRequestMetadata(Google_Service_Partners_RequestMetadata $requestMetadata)
  {
    $this->requestMetadata = $requestMetadata;
  }
  public function getRequestMetadata()
  {
    return $this->requestMetadata;
  }
  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}
