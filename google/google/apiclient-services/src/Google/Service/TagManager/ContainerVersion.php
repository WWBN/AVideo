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

class Google_Service_TagManager_ContainerVersion extends Google_Collection
{
  protected $collection_key = 'variable';
  public $accountId;
  protected $containerType = 'Google_Service_TagManager_Container';
  protected $containerDataType = '';
  public $containerId;
  public $containerVersionId;
  public $deleted;
  public $fingerprint;
  protected $folderType = 'Google_Service_TagManager_Folder';
  protected $folderDataType = 'array';
  protected $macroType = 'Google_Service_TagManager_Macro';
  protected $macroDataType = 'array';
  public $name;
  public $notes;
  protected $ruleType = 'Google_Service_TagManager_Rule';
  protected $ruleDataType = 'array';
  protected $tagType = 'Google_Service_TagManager_Tag';
  protected $tagDataType = 'array';
  protected $triggerType = 'Google_Service_TagManager_Trigger';
  protected $triggerDataType = 'array';
  protected $variableType = 'Google_Service_TagManager_Variable';
  protected $variableDataType = 'array';

  public function setAccountId($accountId)
  {
    $this->accountId = $accountId;
  }
  public function getAccountId()
  {
    return $this->accountId;
  }
  public function setContainer(Google_Service_TagManager_Container $container)
  {
    $this->container = $container;
  }
  public function getContainer()
  {
    return $this->container;
  }
  public function setContainerId($containerId)
  {
    $this->containerId = $containerId;
  }
  public function getContainerId()
  {
    return $this->containerId;
  }
  public function setContainerVersionId($containerVersionId)
  {
    $this->containerVersionId = $containerVersionId;
  }
  public function getContainerVersionId()
  {
    return $this->containerVersionId;
  }
  public function setDeleted($deleted)
  {
    $this->deleted = $deleted;
  }
  public function getDeleted()
  {
    return $this->deleted;
  }
  public function setFingerprint($fingerprint)
  {
    $this->fingerprint = $fingerprint;
  }
  public function getFingerprint()
  {
    return $this->fingerprint;
  }
  public function setFolder($folder)
  {
    $this->folder = $folder;
  }
  public function getFolder()
  {
    return $this->folder;
  }
  public function setMacro($macro)
  {
    $this->macro = $macro;
  }
  public function getMacro()
  {
    return $this->macro;
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
  public function setRule($rule)
  {
    $this->rule = $rule;
  }
  public function getRule()
  {
    return $this->rule;
  }
  public function setTag($tag)
  {
    $this->tag = $tag;
  }
  public function getTag()
  {
    return $this->tag;
  }
  public function setTrigger($trigger)
  {
    $this->trigger = $trigger;
  }
  public function getTrigger()
  {
    return $this->trigger;
  }
  public function setVariable($variable)
  {
    $this->variable = $variable;
  }
  public function getVariable()
  {
    return $this->variable;
  }
}
