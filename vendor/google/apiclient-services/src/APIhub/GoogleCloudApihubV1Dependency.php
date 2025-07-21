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

namespace Google\Service\APIhub;

class GoogleCloudApihubV1Dependency extends \Google\Model
{
  protected $attributesType = GoogleCloudApihubV1AttributeValues::class;
  protected $attributesDataType = 'map';
  protected $consumerType = GoogleCloudApihubV1DependencyEntityReference::class;
  protected $consumerDataType = '';
  /**
   * @var string
   */
  public $createTime;
  /**
   * @var string
   */
  public $description;
  /**
   * @var string
   */
  public $discoveryMode;
  protected $errorDetailType = GoogleCloudApihubV1DependencyErrorDetail::class;
  protected $errorDetailDataType = '';
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $state;
  protected $supplierType = GoogleCloudApihubV1DependencyEntityReference::class;
  protected $supplierDataType = '';
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param GoogleCloudApihubV1AttributeValues[]
   */
  public function setAttributes($attributes)
  {
    $this->attributes = $attributes;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues[]
   */
  public function getAttributes()
  {
    return $this->attributes;
  }
  /**
   * @param GoogleCloudApihubV1DependencyEntityReference
   */
  public function setConsumer(GoogleCloudApihubV1DependencyEntityReference $consumer)
  {
    $this->consumer = $consumer;
  }
  /**
   * @return GoogleCloudApihubV1DependencyEntityReference
   */
  public function getConsumer()
  {
    return $this->consumer;
  }
  /**
   * @param string
   */
  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  /**
   * @return string
   */
  public function getCreateTime()
  {
    return $this->createTime;
  }
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
   * @param string
   */
  public function setDiscoveryMode($discoveryMode)
  {
    $this->discoveryMode = $discoveryMode;
  }
  /**
   * @return string
   */
  public function getDiscoveryMode()
  {
    return $this->discoveryMode;
  }
  /**
   * @param GoogleCloudApihubV1DependencyErrorDetail
   */
  public function setErrorDetail(GoogleCloudApihubV1DependencyErrorDetail $errorDetail)
  {
    $this->errorDetail = $errorDetail;
  }
  /**
   * @return GoogleCloudApihubV1DependencyErrorDetail
   */
  public function getErrorDetail()
  {
    return $this->errorDetail;
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
   * @param string
   */
  public function setState($state)
  {
    $this->state = $state;
  }
  /**
   * @return string
   */
  public function getState()
  {
    return $this->state;
  }
  /**
   * @param GoogleCloudApihubV1DependencyEntityReference
   */
  public function setSupplier(GoogleCloudApihubV1DependencyEntityReference $supplier)
  {
    $this->supplier = $supplier;
  }
  /**
   * @return GoogleCloudApihubV1DependencyEntityReference
   */
  public function getSupplier()
  {
    return $this->supplier;
  }
  /**
   * @param string
   */
  public function setUpdateTime($updateTime)
  {
    $this->updateTime = $updateTime;
  }
  /**
   * @return string
   */
  public function getUpdateTime()
  {
    return $this->updateTime;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1Dependency::class, 'Google_Service_APIhub_GoogleCloudApihubV1Dependency');
