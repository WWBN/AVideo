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

class CreateVersionRequest extends \Google\Collection
{
  protected $collection_key = 'references';
  /**
   * @var int
   */
  public $id;
  /**
   * @var bool
   */
  public $normalize;
  protected $referencesType = SchemaReference::class;
  protected $referencesDataType = 'array';
  /**
   * @var string
   */
  public $schema;
  /**
   * @var string
   */
  public $schemaType;
  /**
   * @var int
   */
  public $version;

  /**
   * @param int
   */
  public function setId($id)
  {
    $this->id = $id;
  }
  /**
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }
  /**
   * @param bool
   */
  public function setNormalize($normalize)
  {
    $this->normalize = $normalize;
  }
  /**
   * @return bool
   */
  public function getNormalize()
  {
    return $this->normalize;
  }
  /**
   * @param SchemaReference[]
   */
  public function setReferences($references)
  {
    $this->references = $references;
  }
  /**
   * @return SchemaReference[]
   */
  public function getReferences()
  {
    return $this->references;
  }
  /**
   * @param string
   */
  public function setSchema($schema)
  {
    $this->schema = $schema;
  }
  /**
   * @return string
   */
  public function getSchema()
  {
    return $this->schema;
  }
  /**
   * @param string
   */
  public function setSchemaType($schemaType)
  {
    $this->schemaType = $schemaType;
  }
  /**
   * @return string
   */
  public function getSchemaType()
  {
    return $this->schemaType;
  }
  /**
   * @param int
   */
  public function setVersion($version)
  {
    $this->version = $version;
  }
  /**
   * @return int
   */
  public function getVersion()
  {
    return $this->version;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CreateVersionRequest::class, 'Google_Service_ManagedKafka_CreateVersionRequest');
