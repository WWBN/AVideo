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

class Google_Service_TagManager_Macro extends Google_Collection
{
  protected $collection_key = 'parameter';
  public $accountId;
  public $containerId;
  public $disablingRuleId;
  public $enablingRuleId;
  public $fingerprint;
  public $macroId;
  public $name;
  public $notes;
  protected $parameterType = 'Google_Service_TagManager_Parameter';
  protected $parameterDataType = 'array';
  public $parentFolderId;
  public $scheduleEndMs;
  public $scheduleStartMs;
  public $type;

  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setContainerId($containerId)
  {
    $this->containerId = $containerId;
  }
  public function getContainerId()
  {
    return $this->containerId;
  }
  public function setDisablingRuleId($disablingRuleId)
  {
    $this->disablingRuleId = $disablingRuleId;
  }
  public function getDisablingRuleId()
  {
    return $this->disablingRuleId;
  }
  public function setEnablingRuleId($enablingRuleId)
  {
    $this->enablingRuleId = $enablingRuleId;
  }
  public function getEnablingRuleId()
  {
    return $this->enablingRuleId;
  }
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setMacroId($macroId)
  {
    $this->macroId = $macroId;
  }
  public function getMacroId()
  {
    return $this->macroId;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setNotes($notes)
  {
    $this->notes = $notes;
  }
  public function getNotes()
  {
    return $this->notes;
  }
  public function setParameter($parameter)
  {
    $this->parameter = $parameter;
  }
  public function getParameter()
  {
    return $this->parameter;
  }
  public function setParentFolderId($parentFolderId)
  {
    $this->parentFolderId = $parentFolderId;
  }
  public function getParentFolderId()
  {
    return $this->parentFolderId;
  }
  public function setScheduleEndMs($scheduleEndMs)
  {
    $this->scheduleEndMs = $scheduleEndMs;
  }
  public function getScheduleEndMs()
  {
    return $this->scheduleEndMs;
  }
  public function setScheduleStartMs($scheduleStartMs)
  {
    $this->scheduleStartMs = $scheduleStartMs;
  }
  public function getScheduleStartMs()
  {
    return $this->scheduleStartMs;
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
