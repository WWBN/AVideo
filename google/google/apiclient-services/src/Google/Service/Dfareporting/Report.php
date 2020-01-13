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

class Google_Service_Dfareporting_Report extends Google_Model
{
  public $accountId;
  protected $criteriaType = 'Google_Service_Dfareporting_ReportCriteria';
  protected $criteriaDataType = '';
  protected $crossDimensionReachCriteriaType = 'Google_Service_Dfareporting_ReportCrossDimensionReachCriteria';
  protected $crossDimensionReachCriteriaDataType = '';
  protected $deliveryType = 'Google_Service_Dfareporting_ReportDelivery';
  protected $deliveryDataType = '';
  public $etag;
  public $fileName;
  protected $floodlightCriteriaType = 'Google_Service_Dfareporting_ReportFloodlightCriteria';
  protected $floodlightCriteriaDataType = '';
  public $format;
  public $id;
  public $kind;
  public $lastModifiedTime;
  public $name;
  public $ownerProfileId;
  protected $pathToConversionCriteriaType = 'Google_Service_Dfareporting_ReportPathToConversionCriteria';
  protected $pathToConversionCriteriaDataType = '';
  protected $reachCriteriaType = 'Google_Service_Dfareporting_ReportReachCriteria';
  protected $reachCriteriaDataType = '';
  protected $scheduleType = 'Google_Service_Dfareporting_ReportSchedule';
  protected $scheduleDataType = '';
  public $subAccountId;
  public $type;

  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setCriteria(Google_Service_Dfareporting_ReportCriteria $criteria)
  {
    $this->criteria = $criteria;
  }
  public function getCriteria()
  {
    return $this->criteria;
  }
  public function setCrossDimensionReachCriteria(Google_Service_Dfareporting_ReportCrossDimensionReachCriteria $crossDimensionReachCriteria)
  {
    $this->crossDimensionReachCriteria = $crossDimensionReachCriteria;
  }
  public function getCrossDimensionReachCriteria()
  {
    return $this->crossDimensionReachCriteria;
  }
  public function setDelivery(Google_Service_Dfareporting_ReportDelivery $delivery)
  {
    $this->delivery = $delivery;
  }
  public function getDelivery()
  {
    return $this->delivery;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setFileName($fileName)
  {
    $this->fileName = $fileName;
  }
  public function getFileName()
  {
    return $this->fileName;
  }
  public function setFloodlightCriteria(Google_Service_Dfareporting_ReportFloodlightCriteria $floodlightCriteria)
  {
    $this->floodlightCriteria = $floodlightCriteria;
  }
  public function getFloodlightCriteria()
  {
    return $this->floodlightCriteria;
  }
  public function setFormat($format)
  {
    $this->format = $format;
  }
  public function getFormat()
  {
    return $this->format;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLastModifiedTime($lastModifiedTime)
  {
    $this->lastModifiedTime = $lastModifiedTime;
  }
  public function getLastModifiedTime()
  {
    return $this->lastModifiedTime;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setOwnerProfileId($ownerProfileId)
  {
    $this->ownerProfileId = $ownerProfileId;
  }
  public function getOwnerProfileId()
  {
    return $this->ownerProfileId;
  }
  public function setPathToConversionCriteria(Google_Service_Dfareporting_ReportPathToConversionCriteria $pathToConversionCriteria)
  {
    $this->pathToConversionCriteria = $pathToConversionCriteria;
  }
  public function getPathToConversionCriteria()
  {
    return $this->pathToConversionCriteria;
  }
  public function setReachCriteria(Google_Service_Dfareporting_ReportReachCriteria $reachCriteria)
  {
    $this->reachCriteria = $reachCriteria;
  }
  public function getReachCriteria()
  {
    return $this->reachCriteria;
  }
  public function setSchedule(Google_Service_Dfareporting_ReportSchedule $schedule)
  {
    $this->schedule = $schedule;
  }
  public function getSchedule()
  {
    return $this->schedule;
  }
  public function setSubAccountId($subAccountId)
  {
    $this->subAccountId = $subAccountId;
  }
  public function getSubAccountId()
  {
    return $this->subAccountId;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
}
