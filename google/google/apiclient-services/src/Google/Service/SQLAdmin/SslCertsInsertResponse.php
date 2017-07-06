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

class Google_Service_SQLAdmin_SslCertsInsertResponse extends Google_Model
{
  protected $clientCertType = 'Google_Service_SQLAdmin_SslCertDetail';
  protected $clientCertDataType = '';
  public $kind;
  protected $operationType = 'Google_Service_SQLAdmin_Operation';
  protected $operationDataType = '';
  protected $serverCaCertType = 'Google_Service_SQLAdmin_SslCert';
  protected $serverCaCertDataType = '';

  public function setClientCert(Google_Service_SQLAdmin_SslCertDetail $clientCert)
  {
    $this->clientCert = $clientCert;
  }
  public function getClientCert()
  {
    return $this->clientCert;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setOperation(Google_Service_SQLAdmin_Operation $operation)
  {
    $this->operation = $operation;
  }
  public function getOperation()
  {
    return $this->operation;
  }
  public function setServerCaCert(Google_Service_SQLAdmin_SslCert $serverCaCert)
  {
    $this->serverCaCert = $serverCaCert;
  }
  public function getServerCaCert()
  {
    return $this->serverCaCert;
  }
}
