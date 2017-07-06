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

class Google_Service_AndroidPublisher_InappproductsBatchRequestEntry extends Google_Model
{
  public $batchId;
  protected $inappproductsinsertrequestType = 'Google_Service_AndroidPublisher_InappproductsInsertRequest';
  protected $inappproductsinsertrequestDataType = '';
  protected $inappproductsupdaterequestType = 'Google_Service_AndroidPublisher_InappproductsUpdateRequest';
  protected $inappproductsupdaterequestDataType = '';
  public $methodName;

  public function setBatchId($batchId)
  {
    $this->batchId = $batchId;
  }
  public function getBatchId()
  {
    return $this->batchId;
  }
  public function setInappproductsinsertrequest(Google_Service_AndroidPublisher_InappproductsInsertRequest $inappproductsinsertrequest)
  {
    $this->inappproductsinsertrequest = $inappproductsinsertrequest;
  }
  public function getInappproductsinsertrequest()
  {
    return $this->inappproductsinsertrequest;
  }
  public function setInappproductsupdaterequest(Google_Service_AndroidPublisher_InappproductsUpdateRequest $inappproductsupdaterequest)
  {
    $this->inappproductsupdaterequest = $inappproductsupdaterequest;
  }
  public function getInappproductsupdaterequest()
  {
    return $this->inappproductsupdaterequest;
  }
  public function setMethodName($methodName)
  {
    $this->methodName = $methodName;
  }
  public function getMethodName()
  {
    return $this->methodName;
  }
}
