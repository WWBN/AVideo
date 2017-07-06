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

class Google_Service_CloudDebugger_Breakpoint extends Google_Collection
{
  protected $collection_key = 'variableTable';
  public $action;
  public $condition;
  public $createTime;
  protected $evaluatedExpressionsType = 'Google_Service_CloudDebugger_Variable';
  protected $evaluatedExpressionsDataType = 'array';
  public $expressions;
  public $finalTime;
  public $id;
  public $isFinalState;
  public $labels;
  protected $locationType = 'Google_Service_CloudDebugger_SourceLocation';
  protected $locationDataType = '';
  public $logLevel;
  public $logMessageFormat;
  protected $stackFramesType = 'Google_Service_CloudDebugger_StackFrame';
  protected $stackFramesDataType = 'array';
  protected $statusType = 'Google_Service_CloudDebugger_StatusMessage';
  protected $statusDataType = '';
  public $userEmail;
  protected $variableTableType = 'Google_Service_CloudDebugger_Variable';
  protected $variableTableDataType = 'array';

  public function setAction($action)
  {
    $this->action = $action;
  }
  public function getAction()
  {
    return $this->action;
  }
  public function setCondition($condition)
  {
    $this->condition = $condition;
  }
  public function getCondition()
  {
    return $this->condition;
  }
  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  public function getCreateTime()
  {
    return $this->createTime;
  }
  public function setEvaluatedExpressions($evaluatedExpressions)
  {
    $this->evaluatedExpressions = $evaluatedExpressions;
  }
  public function getEvaluatedExpressions()
  {
    return $this->evaluatedExpressions;
  }
  public function setExpressions($expressions)
  {
    $this->expressions = $expressions;
  }
  public function getExpressions()
  {
    return $this->expressions;
  }
  public function setFinalTime($finalTime)
  {
    $this->finalTime = $finalTime;
  }
  public function getFinalTime()
  {
    return $this->finalTime;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setIsFinalState($isFinalState)
  {
    $this->isFinalState = $isFinalState;
  }
  public function getIsFinalState()
  {
    return $this->isFinalState;
  }
  public function setLabels($labels)
  {
    $this->labels = $labels;
  }
  public function getLabels()
  {
    return $this->labels;
  }
  public function setLocation(Google_Service_CloudDebugger_SourceLocation $location)
  {
    $this->location = $location;
  }
  public function getLocation()
  {
    return $this->location;
  }
  public function setLogLevel($logLevel)
  {
    $this->logLevel = $logLevel;
  }
  public function getLogLevel()
  {
    return $this->logLevel;
  }
  public function setLogMessageFormat($logMessageFormat)
  {
    $this->logMessageFormat = $logMessageFormat;
  }
  public function getLogMessageFormat()
  {
    return $this->logMessageFormat;
  }
  public function setStackFrames($stackFrames)
  {
    $this->stackFrames = $stackFrames;
  }
  public function getStackFrames()
  {
    return $this->stackFrames;
  }
  public function setStatus(Google_Service_CloudDebugger_StatusMessage $status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setUserEmail($userEmail)
  {
    $this->userEmail = $userEmail;
  }
  public function getUserEmail()
  {
    return $this->userEmail;
  }
  public function setVariableTable($variableTable)
  {
    $this->variableTable = $variableTable;
  }
  public function getVariableTable()
  {
    return $this->variableTable;
  }
}
