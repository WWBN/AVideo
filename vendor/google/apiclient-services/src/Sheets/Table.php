<?php
/*
 * Copyright 2014 Google Inc.
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

namespace Google\Service\Sheets;

class Table extends \Google\Collection
{
  protected $collection_key = 'columnProperties';
  protected $columnPropertiesType = TableColumnProperties::class;
  protected $columnPropertiesDataType = 'array';
  /**
   * @var string
   */
  public $name;
  protected $rangeType = GridRange::class;
  protected $rangeDataType = '';
  protected $rowsPropertiesType = TableRowsProperties::class;
  protected $rowsPropertiesDataType = '';
  /**
   * @var string
   */
  public $tableId;

  /**
   * @param TableColumnProperties[]
   */
  public function setColumnProperties($columnProperties)
  {
    $this->columnProperties = $columnProperties;
  }
  /**
   * @return TableColumnProperties[]
   */
  public function getColumnProperties()
  {
    return $this->columnProperties;
  }
  /**
   * @param string
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * @param GridRange
   */
  public function setRange(GridRange $range)
  {
    $this->range = $range;
  }
  /**
   * @return GridRange
   */
  public function getRange()
  {
    return $this->range;
  }
  /**
   * @param TableRowsProperties
   */
  public function setRowsProperties(TableRowsProperties $rowsProperties)
  {
    $this->rowsProperties = $rowsProperties;
  }
  /**
   * @return TableRowsProperties
   */
  public function getRowsProperties()
  {
    return $this->rowsProperties;
  }
  /**
   * @param string
   */
  public function setTableId($tableId)
  {
    $this->tableId = $tableId;
  }
  /**
   * @return string
   */
  public function getTableId()
  {
    return $this->tableId;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Table::class, 'Google_Service_Sheets_Table');
