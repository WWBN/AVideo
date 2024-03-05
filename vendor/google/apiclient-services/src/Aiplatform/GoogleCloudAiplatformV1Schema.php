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

namespace Google\Service\Aiplatform;

class GoogleCloudAiplatformV1Schema extends \Google\Collection
{
  protected $collection_key = 'required';
  /**
   * @var string
   */
  public $description;
  /**
   * @var string[]
   */
  public $enum;
  /**
   * @var array
   */
  public $example;
  /**
   * @var string
   */
  public $format;
  protected $itemsType = GoogleCloudAiplatformV1Schema::class;
  protected $itemsDataType = '';
  /**
   * @var bool
   */
  public $nullable;
  protected $propertiesType = GoogleCloudAiplatformV1Schema::class;
  protected $propertiesDataType = 'map';
  /**
   * @var string[]
   */
  public $required;
  /**
   * @var string
   */
  public $type;

  /**
   * @param string
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }
  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }
  /**
   * @param string[]
   */
  public function setEnum($enum)
  {
    $this->enum = $enum;
  }
  /**
   * @return string[]
   */
  public function getEnum()
  {
    return $this->enum;
  }
  /**
   * @param array
   */
  public function setExample($example)
  {
    $this->example = $example;
  }
  /**
   * @return array
   */
  public function getExample()
  {
    return $this->example;
  }
  /**
   * @param string
   */
  public function setFormat($format)
  {
    $this->format = $format;
  }
  /**
   * @return string
   */
  public function getFormat()
  {
    return $this->format;
  }
  /**
   * @param GoogleCloudAiplatformV1Schema
   */
  public function setItems(GoogleCloudAiplatformV1Schema $items)
  {
    $this->items = $items;
  }
  /**
   * @return GoogleCloudAiplatformV1Schema
   */
  public function getItems()
  {
    return $this->items;
  }
  /**
   * @param bool
   */
  public function setNullable($nullable)
  {
    $this->nullable = $nullable;
  }
  /**
   * @return bool
   */
  public function getNullable()
  {
    return $this->nullable;
  }
  /**
   * @param GoogleCloudAiplatformV1Schema[]
   */
  public function setProperties($properties)
  {
    $this->properties = $properties;
  }
  /**
   * @return GoogleCloudAiplatformV1Schema[]
   */
  public function getProperties()
  {
    return $this->properties;
  }
  /**
   * @param string[]
   */
  public function setRequired($required)
  {
    $this->required = $required;
  }
  /**
   * @return string[]
   */
  public function getRequired()
  {
    return $this->required;
  }
  /**
   * @param string
   */
  public function setType($type)
  {
    $this->type = $type;
  }
  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudAiplatformV1Schema::class, 'Google_Service_Aiplatform_GoogleCloudAiplatformV1Schema');
