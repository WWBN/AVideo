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

namespace Google\Service\ManagedKafka;

class CreateSchemaRegistryRequest extends \Google\Model
{
  protected $schemaRegistryType = SchemaRegistry::class;
  protected $schemaRegistryDataType = '';
  /**
   * @var string
   */
  public $schemaRegistryId;

  /**
   * @param SchemaRegistry
   */
  public function setSchemaRegistry(SchemaRegistry $schemaRegistry)
  {
    $this->schemaRegistry = $schemaRegistry;
  }
  /**
   * @return SchemaRegistry
   */
  public function getSchemaRegistry()
  {
    return $this->schemaRegistry;
  }
  /**
   * @param string
   */
  public function setSchemaRegistryId($schemaRegistryId)
  {
    $this->schemaRegistryId = $schemaRegistryId;
  }
  /**
   * @return string
   */
  public function getSchemaRegistryId()
  {
    return $this->schemaRegistryId;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CreateSchemaRegistryRequest::class, 'Google_Service_ManagedKafka_CreateSchemaRegistryRequest');
