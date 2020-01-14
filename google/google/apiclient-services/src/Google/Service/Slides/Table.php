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

class Google_Service_Slides_Table extends Google_Collection
{
  protected $collection_key = 'tableRows';
  public $columns;
  public $rows;
  protected $tableColumnsType = 'Google_Service_Slides_TableColumnProperties';
  protected $tableColumnsDataType = 'array';
  protected $tableRowsType = 'Google_Service_Slides_TableRow';
  protected $tableRowsDataType = 'array';

  public function setColumns($columns)
  {
    $this->columns = $columns;
  }
  public function getColumns()
  {
    return $this->columns;
  }
  public function setRows($rows)
  {
    $this->rows = $rows;
  }
  public function getRows()
  {
    return $this->rows;
  }
  public function setTableColumns($tableColumns)
  {
    $this->tableColumns = $tableColumns;
  }
  public function getTableColumns()
  {
    return $this->tableColumns;
  }
  public function setTableRows($tableRows)
  {
    $this->tableRows = $tableRows;
  }
  public function getTableRows()
  {
    return $this->tableRows;
  }
}
