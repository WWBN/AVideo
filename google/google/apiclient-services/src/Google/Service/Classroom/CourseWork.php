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

class Google_Service_Classroom_CourseWork extends Google_Collection
{
  protected $collection_key = 'materials';
  public $alternateLink;
  protected $assignmentType = 'Google_Service_Classroom_Assignment';
  protected $assignmentDataType = '';
  public $associatedWithDeveloper;
  public $courseId;
  public $creationTime;
  public $description;
  protected $dueDateType = 'Google_Service_Classroom_Date';
  protected $dueDateDataType = '';
  protected $dueTimeType = 'Google_Service_Classroom_TimeOfDay';
  protected $dueTimeDataType = '';
  public $id;
  protected $materialsType = 'Google_Service_Classroom_Material';
  protected $materialsDataType = 'array';
  public $maxPoints;
  protected $multipleChoiceQuestionType = 'Google_Service_Classroom_MultipleChoiceQuestion';
  protected $multipleChoiceQuestionDataType = '';
  public $state;
  public $submissionModificationMode;
  public $title;
  public $updateTime;
  public $workType;

  public function setAlternateLink($alternateLink)
  {
    $this->alternateLink = $alternateLink;
  }
  public function getAlternateLink()
  {
    return $this->alternateLink;
  }
  public function setAssignment(Google_Service_Classroom_Assignment $assignment)
  {
    $this->assignment = $assignment;
  }
  public function getAssignment()
  {
    return $this->assignment;
  }
  public function setAssociatedWithDeveloper($associatedWithDeveloper)
  {
    $this->associatedWithDeveloper = $associatedWithDeveloper;
  }
  public function getAssociatedWithDeveloper()
  {
    return $this->associatedWithDeveloper;
  }
  public function setCourseId($courseId)
  {
    $this->courseId = $courseId;
  }
  public function getCourseId()
  {
    return $this->courseId;
  }
  public function setCreationTime($creationTime)
  {
    $this->creationTime = $creationTime;
  }
  public function getCreationTime()
  {
    return $this->creationTime;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setDueDate(Google_Service_Classroom_Date $dueDate)
  {
    $this->dueDate = $dueDate;
  }
  public function getDueDate()
  {
    return $this->dueDate;
  }
  public function setDueTime(Google_Service_Classroom_TimeOfDay $dueTime)
  {
    $this->dueTime = $dueTime;
  }
  public function getDueTime()
  {
    return $this->dueTime;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setMaterials($materials)
  {
    $this->materials = $materials;
  }
  public function getMaterials()
  {
    return $this->materials;
  }
  public function setMaxPoints($maxPoints)
  {
    $this->maxPoints = $maxPoints;
  }
  public function getMaxPoints()
  {
    return $this->maxPoints;
  }
  public function setMultipleChoiceQuestion(Google_Service_Classroom_MultipleChoiceQuestion $multipleChoiceQuestion)
  {
    $this->multipleChoiceQuestion = $multipleChoiceQuestion;
  }
  public function getMultipleChoiceQuestion()
  {
    return $this->multipleChoiceQuestion;
  }
  public function setState($state)
  {
    $this->state = $state;
  }
  public function getState()
  {
    return $this->state;
  }
  public function setSubmissionModificationMode($submissionModificationMode)
  {
    $this->submissionModificationMode = $submissionModificationMode;
  }
  public function getSubmissionModificationMode()
  {
    return $this->submissionModificationMode;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
  public function setUpdateTime($updateTime)
  {
    $this->updateTime = $updateTime;
  }
  public function getUpdateTime()
  {
    return $this->updateTime;
  }
  public function setWorkType($workType)
  {
    $this->workType = $workType;
  }
  public function getWorkType()
  {
    return $this->workType;
  }
}
