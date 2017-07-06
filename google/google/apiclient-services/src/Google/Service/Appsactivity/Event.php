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

class Google_Service_Appsactivity_Event extends Google_Collection
{
  protected $collection_key = 'permissionChanges';
  public $additionalEventTypes;
  public $eventTimeMillis;
  public $fromUserDeletion;
  protected $moveType = 'Google_Service_Appsactivity_Move';
  protected $moveDataType = '';
  protected $permissionChangesType = 'Google_Service_Appsactivity_PermissionChange';
  protected $permissionChangesDataType = 'array';
  public $primaryEventType;
  protected $renameType = 'Google_Service_Appsactivity_Rename';
  protected $renameDataType = '';
  protected $targetType = 'Google_Service_Appsactivity_Target';
  protected $targetDataType = '';
  protected $userType = 'Google_Service_Appsactivity_User';
  protected $userDataType = '';

  public function setAdditionalEventTypes($additionalEventTypes)
  {
    $this->additionalEventTypes = $additionalEventTypes;
  }
  public function getAdditionalEventTypes()
  {
    return $this->additionalEventTypes;
  }
  public function setEventTimeMillis($eventTimeMillis)
  {
    $this->eventTimeMillis = $eventTimeMillis;
  }
  public function getEventTimeMillis()
  {
    return $this->eventTimeMillis;
  }
  public function setFromUserDeletion($fromUserDeletion)
  {
    $this->fromUserDeletion = $fromUserDeletion;
  }
  public function getFromUserDeletion()
  {
    return $this->fromUserDeletion;
  }
  public function setMove(Google_Service_Appsactivity_Move $move)
  {
    $this->move = $move;
  }
  public function getMove()
  {
    return $this->move;
  }
  public function setPermissionChanges($permissionChanges)
  {
    $this->permissionChanges = $permissionChanges;
  }
  public function getPermissionChanges()
  {
    return $this->permissionChanges;
  }
  public function setPrimaryEventType($primaryEventType)
  {
    $this->primaryEventType = $primaryEventType;
  }
  public function getPrimaryEventType()
  {
    return $this->primaryEventType;
  }
  public function setRename(Google_Service_Appsactivity_Rename $rename)
  {
    $this->rename = $rename;
  }
  public function getRename()
  {
    return $this->rename;
  }
  public function setTarget(Google_Service_Appsactivity_Target $target)
  {
    $this->target = $target;
  }
  public function getTarget()
  {
    return $this->target;
  }
  public function setUser(Google_Service_Appsactivity_User $user)
  {
    $this->user = $user;
  }
  public function getUser()
  {
    return $this->user;
  }
}
