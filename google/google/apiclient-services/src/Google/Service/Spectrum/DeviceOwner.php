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

class Google_Service_Spectrum_DeviceOwner extends Google_Model
{
  protected $operatorType = 'Google_Service_Spectrum_Vcard';
  protected $operatorDataType = '';
  protected $ownerType = 'Google_Service_Spectrum_Vcard';
  protected $ownerDataType = '';

  public function setOperator(Google_Service_Spectrum_Vcard $operator)
  {
    $this->operator = $operator;
  }
  public function getOperator()
  {
    return $this->operator;
  }
  public function setOwner(Google_Service_Spectrum_Vcard $owner)
  {
    $this->owner = $owner;
  }
  public function getOwner()
  {
    return $this->owner;
  }
}
