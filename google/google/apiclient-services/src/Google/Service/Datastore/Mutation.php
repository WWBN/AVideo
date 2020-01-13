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

class Google_Service_Datastore_Mutation extends Google_Model
{
  public $baseVersion;
  protected $deleteType = 'Google_Service_Datastore_Key';
  protected $deleteDataType = '';
  protected $insertType = 'Google_Service_Datastore_Entity';
  protected $insertDataType = '';
  protected $updateType = 'Google_Service_Datastore_Entity';
  protected $updateDataType = '';
  protected $upsertType = 'Google_Service_Datastore_Entity';
  protected $upsertDataType = '';

  public function setBaseVersion($baseVersion)
  {
    $this->baseVersion = $baseVersion;
  }
  public function getBaseVersion()
  {
    return $this->baseVersion;
  }
  public function setDelete(Google_Service_Datastore_Key $delete)
  {
    $this->delete = $delete;
  }
  public function getDelete()
  {
    return $this->delete;
  }
  public function setInsert(Google_Service_Datastore_Entity $insert)
  {
    $this->insert = $insert;
  }
  public function getInsert()
  {
    return $this->insert;
  }
  public function setUpdate(Google_Service_Datastore_Entity $update)
  {
    $this->update = $update;
  }
  public function getUpdate()
  {
    return $this->update;
  }
  public function setUpsert(Google_Service_Datastore_Entity $upsert)
  {
    $this->upsert = $upsert;
  }
  public function getUpsert()
  {
    return $this->upsert;
  }
}
