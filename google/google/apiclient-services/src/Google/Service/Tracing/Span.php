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

class Google_Service_Tracing_Span extends Google_Collection
{
  protected $collection_key = 'timeEvents';
  protected $attributesType = 'Google_Service_Tracing_AttributeValue';
  protected $attributesDataType = 'map';
  public $hasRemoteParent;
  public $id;
  protected $linksType = 'Google_Service_Tracing_Link';
  protected $linksDataType = 'array';
  public $localEndTime;
  public $localStartTime;
  public $name;
  public $parentId;
  protected $stackTraceType = 'Google_Service_Tracing_StackTrace';
  protected $stackTraceDataType = '';
  protected $statusType = 'Google_Service_Tracing_Status';
  protected $statusDataType = '';
  protected $timeEventsType = 'Google_Service_Tracing_TimeEvent';
  protected $timeEventsDataType = 'array';

  public function setAttributes($attributes)
  {
    $this->attributes = $attributes;
  }
  public function getAttributes()
  {
    return $this->attributes;
  }
  public function setHasRemoteParent($hasRemoteParent)
  {
    $this->hasRemoteParent = $hasRemoteParent;
  }
  public function getHasRemoteParent()
  {
    return $this->hasRemoteParent;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setLinks($links)
  {
    $this->links = $links;
  }
  public function getLinks()
  {
    return $this->links;
  }
  public function setLocalEndTime($localEndTime)
  {
    $this->localEndTime = $localEndTime;
  }
  public function getLocalEndTime()
  {
    return $this->localEndTime;
  }
  public function setLocalStartTime($localStartTime)
  {
    $this->localStartTime = $localStartTime;
  }
  public function getLocalStartTime()
  {
    return $this->localStartTime;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setParentId($parentId)
  {
    $this->parentId = $parentId;
  }
  public function getParentId()
  {
    return $this->parentId;
  }
  public function setStackTrace(Google_Service_Tracing_StackTrace $stackTrace)
  {
    $this->stackTrace = $stackTrace;
  }
  public function getStackTrace()
  {
    return $this->stackTrace;
  }
  public function setStatus(Google_Service_Tracing_Status $status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setTimeEvents($timeEvents)
  {
    $this->timeEvents = $timeEvents;
  }
  public function getTimeEvents()
  {
    return $this->timeEvents;
  }
}
