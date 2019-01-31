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

class Google_Service_CivicInfo_RepresentativeInfoResponse extends Google_Collection
{
  protected $collection_key = 'officials';
  protected $divisionsType = 'Google_Service_CivicInfo_GeographicDivision';
  protected $divisionsDataType = 'map';
  public $kind;
  protected $normalizedInputType = 'Google_Service_CivicInfo_SimpleAddressType';
  protected $normalizedInputDataType = '';
  protected $officesType = 'Google_Service_CivicInfo_Office';
  protected $officesDataType = 'array';
  protected $officialsType = 'Google_Service_CivicInfo_Official';
  protected $officialsDataType = 'array';

  public function setDivisions($divisions)
  {
    $this->divisions = $divisions;
  }
  public function getDivisions()
  {
    return $this->divisions;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setNormalizedInput(Google_Service_CivicInfo_SimpleAddressType $normalizedInput)
  {
    $this->normalizedInput = $normalizedInput;
  }
  public function getNormalizedInput()
  {
    return $this->normalizedInput;
  }
  public function setOffices($offices)
  {
    $this->offices = $offices;
  }
  public function getOffices()
  {
    return $this->offices;
  }
  public function setOfficials($officials)
  {
    $this->officials = $officials;
  }
  public function getOfficials()
  {
    return $this->officials;
  }
}
