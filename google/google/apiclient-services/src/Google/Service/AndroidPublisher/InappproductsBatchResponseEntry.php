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

class Google_Service_AndroidPublisher_InappproductsBatchResponseEntry extends Google_Model
{
  public $batchId;
  protected $inappproductsinsertresponseType = 'Google_Service_AndroidPublisher_InappproductsInsertResponse';
  protected $inappproductsinsertresponseDataType = '';
  protected $inappproductsupdateresponseType = 'Google_Service_AndroidPublisher_InappproductsUpdateResponse';
  protected $inappproductsupdateresponseDataType = '';

  public function setBatchId($batchId)
  {
    $this->batchId = $batchId;
  }
  public function getBatchId()
  {
    return $this->batchId;
  }
  public function setInappproductsinsertresponse(Google_Service_AndroidPublisher_InappproductsInsertResponse $inappproductsinsertresponse)
  {
    $this->inappproductsinsertresponse = $inappproductsinsertresponse;
  }
  public function getInappproductsinsertresponse()
  {
    return $this->inappproductsinsertresponse;
  }
  public function setInappproductsupdateresponse(Google_Service_AndroidPublisher_InappproductsUpdateResponse $inappproductsupdateresponse)
  {
    $this->inappproductsupdateresponse = $inappproductsupdateresponse;
  }
  public function getInappproductsupdateresponse()
  {
    return $this->inappproductsupdateresponse;
  }
}
