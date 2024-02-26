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

namespace Google\Service\DatabaseMigrationService;

class DatabaseEntity extends \Google\Collection
{
  protected $collection_key = 'mappings';
  protected $databaseFunctionType = FunctionEntity::class;
  protected $databaseFunctionDataType = '';
  protected $databasePackageType = PackageEntity::class;
  protected $databasePackageDataType = '';
  /**
   * @var string
   */
  public $entityType;
  protected $mappingsType = EntityMapping::class;
  protected $mappingsDataType = 'array';
  /**
   * @var string
   */
  public $parentEntity;
  protected $schemaType = SchemaEntity::class;
  protected $schemaDataType = '';
  protected $sequenceType = SequenceEntity::class;
  protected $sequenceDataType = '';
  /**
   * @var string
   */
  public $shortName;
  protected $storedProcedureType = StoredProcedureEntity::class;
  protected $storedProcedureDataType = '';
  protected $synonymType = SynonymEntity::class;
  protected $synonymDataType = '';
  protected $tableType = TableEntity::class;
  protected $tableDataType = '';
  /**
   * @var string
   */
  public $tree;
  protected $viewType = ViewEntity::class;
  protected $viewDataType = '';

  /**
   * @param FunctionEntity
   */
  public function setDatabaseFunction(FunctionEntity $databaseFunction)
  {
    $this->databaseFunction = $databaseFunction;
  }
  /**
   * @return FunctionEntity
   */
  public function getDatabaseFunction()
  {
    return $this->databaseFunction;
  }
  /**
   * @param PackageEntity
   */
  public function setDatabasePackage(PackageEntity $databasePackage)
  {
    $this->databasePackage = $databasePackage;
  }
  /**
   * @return PackageEntity
   */
  public function getDatabasePackage()
  {
    return $this->databasePackage;
  }
  /**
   * @param string
   */
  public function setEntityType($entityType)
  {
    $this->entityType = $entityType;
  }
  /**
   * @return string
   */
  public function getEntityType()
  {
    return $this->entityType;
  }
  /**
   * @param EntityMapping[]
   */
  public function setMappings($mappings)
  {
    $this->mappings = $mappings;
  }
  /**
   * @return EntityMapping[]
   */
  public function getMappings()
  {
    return $this->mappings;
  }
  /**
   * @param string
   */
  public function setParentEntity($parentEntity)
  {
    $this->parentEntity = $parentEntity;
  }
  /**
   * @return string
   */
  public function getParentEntity()
  {
    return $this->parentEntity;
  }
  /**
   * @param SchemaEntity
   */
  public function setSchema(SchemaEntity $schema)
  {
    $this->schema = $schema;
  }
  /**
   * @return SchemaEntity
   */
  public function getSchema()
  {
    return $this->schema;
  }
  /**
   * @param SequenceEntity
   */
  public function setSequence(SequenceEntity $sequence)
  {
    $this->sequence = $sequence;
  }
  /**
   * @return SequenceEntity
   */
  public function getSequence()
  {
    return $this->sequence;
  }
  /**
   * @param string
   */
  public function setShortName($shortName)
  {
    $this->shortName = $shortName;
  }
  /**
   * @return string
   */
  public function getShortName()
  {
    return $this->shortName;
  }
  /**
   * @param StoredProcedureEntity
   */
  public function setStoredProcedure(StoredProcedureEntity $storedProcedure)
  {
    $this->storedProcedure = $storedProcedure;
  }
  /**
   * @return StoredProcedureEntity
   */
  public function getStoredProcedure()
  {
    return $this->storedProcedure;
  }
  /**
   * @param SynonymEntity
   */
  public function setSynonym(SynonymEntity $synonym)
  {
    $this->synonym = $synonym;
  }
  /**
   * @return SynonymEntity
   */
  public function getSynonym()
  {
    return $this->synonym;
  }
  /**
   * @param TableEntity
   */
  public function setTable(TableEntity $table)
  {
    $this->table = $table;
  }
  /**
   * @return TableEntity
   */
  public function getTable()
  {
    return $this->table;
  }
  /**
   * @param string
   */
  public function setTree($tree)
  {
    $this->tree = $tree;
  }
  /**
   * @return string
   */
  public function getTree()
  {
    return $this->tree;
  }
  /**
   * @param ViewEntity
   */
  public function setView(ViewEntity $view)
  {
    $this->view = $view;
  }
  /**
   * @return ViewEntity
   */
  public function getView()
  {
    return $this->view;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(DatabaseEntity::class, 'Google_Service_DatabaseMigrationService_DatabaseEntity');
