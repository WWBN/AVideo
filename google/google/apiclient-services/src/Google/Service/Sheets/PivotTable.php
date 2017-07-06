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

class Google_Service_Sheets_PivotTable extends Google_Collection
{
  protected $collection_key = 'values';
  protected $columnsType = 'Google_Service_Sheets_PivotGroup';
  protected $columnsDataType = 'array';
  protected $criteriaType = 'Google_Service_Sheets_PivotFilterCriteria';
  protected $criteriaDataType = 'map';
  protected $rowsType = 'Google_Service_Sheets_PivotGroup';
  protected $rowsDataType = 'array';
  protected $sourceType = 'Google_Service_Sheets_GridRange';
  protected $sourceDataType = '';
  public $valueLayout;
  protected $valuesType = 'Google_Service_Sheets_PivotValue';
  protected $valuesDataType = 'array';

  public function setColumns($columns)
  {
    $this->columns = $columns;
  }
  public function getColumns()
  {
    return $this->columns;
  }
  public function setCriteria($criteria)
  {
    $this->criteria = $criteria;
  }
  public function getCriteria()
  {
    return $this->criteria;
  }
  public function setRows($rows)
  {
    $this->rows = $rows;
  }
  public function getRows()
  {
    return $this->rows;
  }
  public function setSource(Google_Service_Sheets_GridRange $source)
  {
    $this->source = $source;
  }
  public function getSource()
  {
    return $this->source;
  }
  public function setValueLayout($valueLayout)
  {
    $this->valueLayout = $valueLayout;
  }
  public function getValueLayout()
  {
    return $this->valueLayout;
  }
  public function setValues($values)
  {
    $this->values = $values;
  }
  public function getValues()
  {
    return $this->values;
  }
}
