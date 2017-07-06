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

class Google_Service_Gmail_Filter extends Google_Model
{
  protected $actionType = 'Google_Service_Gmail_FilterAction';
  protected $actionDataType = '';
  protected $criteriaType = 'Google_Service_Gmail_FilterCriteria';
  protected $criteriaDataType = '';
  public $id;

  public function setAction(Google_Service_Gmail_FilterAction $action)
  {
    $this->action = $action;
  }
  public function getAction()
  {
    return $this->action;
  }
  public function setCriteria(Google_Service_Gmail_FilterCriteria $criteria)
  {
    $this->criteria = $criteria;
  }
  public function getCriteria()
  {
    return $this->criteria;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
}
