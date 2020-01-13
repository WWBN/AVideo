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

class Google_Service_Datastore_Value extends Google_Model
{
  protected $arrayValueType = 'Google_Service_Datastore_ArrayValue';
  protected $arrayValueDataType = '';
  public $blobValue;
  public $booleanValue;
  public $doubleValue;
  protected $entityValueType = 'Google_Service_Datastore_Entity';
  protected $entityValueDataType = '';
  public $excludeFromIndexes;
  protected $geoPointValueType = 'Google_Service_Datastore_LatLng';
  protected $geoPointValueDataType = '';
  public $integerValue;
  protected $keyValueType = 'Google_Service_Datastore_Key';
  protected $keyValueDataType = '';
  public $meaning;
  public $nullValue;
  public $stringValue;
  public $timestampValue;

  public function setArrayValue(Google_Service_Datastore_ArrayValue $arrayValue)
  {
    $this->arrayValue = $arrayValue;
  }
  public function getArrayValue()
  {
    return $this->arrayValue;
  }
  public function setBlobValue($blobValue)
  {
    $this->blobValue = $blobValue;
  }
  public function getBlobValue()
  {
    return $this->blobValue;
  }
  public function setBooleanValue($booleanValue)
  {
    $this->booleanValue = $booleanValue;
  }
  public function getBooleanValue()
  {
    return $this->booleanValue;
  }
  public function setDoubleValue($doubleValue)
  {
    $this->doubleValue = $doubleValue;
  }
  public function getDoubleValue()
  {
    return $this->doubleValue;
  }
  public function setEntityValue(Google_Service_Datastore_Entity $entityValue)
  {
    $this->entityValue = $entityValue;
  }
  public function getEntityValue()
  {
    return $this->entityValue;
  }
  public function setExcludeFromIndexes($excludeFromIndexes)
  {
    $this->excludeFromIndexes = $excludeFromIndexes;
  }
  public function getExcludeFromIndexes()
  {
    return $this->excludeFromIndexes;
  }
  public function setGeoPointValue(Google_Service_Datastore_LatLng $geoPointValue)
  {
    $this->geoPointValue = $geoPointValue;
  }
  public function getGeoPointValue()
  {
    return $this->geoPointValue;
  }
  public function setIntegerValue($integerValue)
  {
    $this->integerValue = $integerValue;
  }
  public function getIntegerValue()
  {
    return $this->integerValue;
  }
  public function setKeyValue(Google_Service_Datastore_Key $keyValue)
  {
    $this->keyValue = $keyValue;
  }
  public function getKeyValue()
  {
    return $this->keyValue;
  }
  public function setMeaning($meaning)
  {
    $this->meaning = $meaning;
  }
  public function getMeaning()
  {
    return $this->meaning;
  }
  public function setNullValue($nullValue)
  {
    $this->nullValue = $nullValue;
  }
  public function getNullValue()
  {
    return $this->nullValue;
  }
  public function setStringValue($stringValue)
  {
    $this->stringValue = $stringValue;
  }
  public function getStringValue()
  {
    return $this->stringValue;
  }
  public function setTimestampValue($timestampValue)
  {
    $this->timestampValue = $timestampValue;
  }
  public function getTimestampValue()
  {
    return $this->timestampValue;
  }
}
