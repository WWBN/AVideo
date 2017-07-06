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

class Google_Service_PlayMovies_Component extends Google_Collection
{
  protected $collection_key = 'titleLevelEidrs';
  public $altCutIds;
  public $approvedTime;
  public $componentDetailType;
  public $componentId;
  public $customIds;
  public $editLevelEidrs;
  public $elIds;
  public $filename;
  public $language;
  public $name;
  public $normalizedPriority;
  public $playableUnitType;
  public $pphName;
  public $priority;
  public $processingErrors;
  public $receivedTime;
  public $rejectionNote;
  public $status;
  public $statusDetail;
  public $studioName;
  public $titleLevelEidrs;
  public $type;

  public function setAltCutIds($altCutIds)
  {
    $this->altCutIds = $altCutIds;
  }
  public function getAltCutIds()
  {
    return $this->altCutIds;
  }
  public function setApprovedTime($approvedTime)
  {
    $this->approvedTime = $approvedTime;
  }
  public function getApprovedTime()
  {
    return $this->approvedTime;
  }
  public function setComponentDetailType($componentDetailType)
  {
    $this->componentDetailType = $componentDetailType;
  }
  public function getComponentDetailType()
  {
    return $this->componentDetailType;
  }
  public function setComponentId($componentId)
  {
    $this->componentId = $componentId;
  }
  public function getComponentId()
  {
    return $this->componentId;
  }
  public function setCustomIds($customIds)
  {
    $this->customIds = $customIds;
  }
  public function getCustomIds()
  {
    return $this->customIds;
  }
  public function setEditLevelEidrs($editLevelEidrs)
  {
    $this->editLevelEidrs = $editLevelEidrs;
  }
  public function getEditLevelEidrs()
  {
    return $this->editLevelEidrs;
  }
  public function setElIds($elIds)
  {
    $this->elIds = $elIds;
  }
  public function getElIds()
  {
    return $this->elIds;
  }
  public function setFilename($filename)
  {
    $this->filename = $filename;
  }
  public function getFilename()
  {
    return $this->filename;
  }
  public function setLanguage($language)
  {
    $this->language = $language;
  }
  public function getLanguage()
  {
    return $this->language;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setNormalizedPriority($normalizedPriority)
  {
    $this->normalizedPriority = $normalizedPriority;
  }
  public function getNormalizedPriority()
  {
    return $this->normalizedPriority;
  }
  public function setPlayableUnitType($playableUnitType)
  {
    $this->playableUnitType = $playableUnitType;
  }
  public function getPlayableUnitType()
  {
    return $this->playableUnitType;
  }
  public function setPphName($pphName)
  {
    $this->pphName = $pphName;
  }
  public function getPphName()
  {
    return $this->pphName;
  }
  public function setPriority($priority)
  {
    $this->priority = $priority;
  }
  public function getPriority()
  {
    return $this->priority;
  }
  public function setProcessingErrors($processingErrors)
  {
    $this->processingErrors = $processingErrors;
  }
  public function getProcessingErrors()
  {
    return $this->processingErrors;
  }
  public function setReceivedTime($receivedTime)
  {
    $this->receivedTime = $receivedTime;
  }
  public function getReceivedTime()
  {
    return $this->receivedTime;
  }
  public function setRejectionNote($rejectionNote)
  {
    $this->rejectionNote = $rejectionNote;
  }
  public function getRejectionNote()
  {
    return $this->rejectionNote;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setStatusDetail($statusDetail)
  {
    $this->statusDetail = $statusDetail;
  }
  public function getStatusDetail()
  {
    return $this->statusDetail;
  }
  public function setStudioName($studioName)
  {
    $this->studioName = $studioName;
  }
  public function getStudioName()
  {
    return $this->studioName;
  }
  public function setTitleLevelEidrs($titleLevelEidrs)
  {
    $this->titleLevelEidrs = $titleLevelEidrs;
  }
  public function getTitleLevelEidrs()
  {
    return $this->titleLevelEidrs;
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
