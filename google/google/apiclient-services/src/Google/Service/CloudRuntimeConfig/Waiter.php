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

class Google_Service_CloudRuntimeConfig_Waiter extends Google_Model
{
  public $createTime;
  public $done;
  protected $errorType = 'Google_Service_CloudRuntimeConfig_Status';
  protected $errorDataType = '';
  protected $failureType = 'Google_Service_CloudRuntimeConfig_EndCondition';
  protected $failureDataType = '';
  public $name;
  protected $successType = 'Google_Service_CloudRuntimeConfig_EndCondition';
  protected $successDataType = '';
  public $timeout;

  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  public function getCreateTime()
  {
    return $this->createTime;
  }
  public function setDone($done)
  {
    $this->done = $done;
  }
  public function getDone()
  {
    return $this->done;
  }
  public function setError(Google_Service_CloudRuntimeConfig_Status $error)
  {
    $this->error = $error;
  }
  public function getError()
  {
    return $this->error;
  }
  public function setFailure(Google_Service_CloudRuntimeConfig_EndCondition $failure)
  {
    $this->failure = $failure;
  }
  public function getFailure()
  {
    return $this->failure;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setSuccess(Google_Service_CloudRuntimeConfig_EndCondition $success)
  {
    $this->success = $success;
  }
  public function getSuccess()
  {
    return $this->success;
  }
  public function setTimeout($timeout)
  {
    $this->timeout = $timeout;
  }
  public function getTimeout()
  {
    return $this->timeout;
  }
}
