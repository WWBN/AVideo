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

class ConnectCluster extends \Google\Model
{
  protected $capacityConfigType = CapacityConfig::class;
  protected $capacityConfigDataType = '';
  /**
   * @var string[]
   */
  public $config;
  /**
   * @var string
   */
  public $createTime;
  protected $gcpConfigType = ConnectGcpConfig::class;
  protected $gcpConfigDataType = '';
  /**
   * @var string
   */
  public $kafkaCluster;
  /**
   * @var string[]
   */
  public $labels;
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $state;
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param CapacityConfig
   */
  public function setCapacityConfig(CapacityConfig $capacityConfig)
  {
    $this->capacityConfig = $capacityConfig;
  }
  /**
   * @return CapacityConfig
   */
  public function getCapacityConfig()
  {
    return $this->capacityConfig;
  }
  /**
   * @param string[]
   */
  public function setConfig($config)
  {
    $this->config = $config;
  }
  /**
   * @return string[]
   */
  public function getConfig()
  {
    return $this->config;
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
   * @param ConnectGcpConfig
   */
  public function setGcpConfig(ConnectGcpConfig $gcpConfig)
  {
    $this->gcpConfig = $gcpConfig;
  }
  /**
   * @return ConnectGcpConfig
   */
  public function getGcpConfig()
  {
    return $this->gcpConfig;
  }
  /**
   * @param string
   */
  public function setKafkaCluster($kafkaCluster)
  {
    $this->kafkaCluster = $kafkaCluster;
  }
  /**
   * @return string
   */
  public function getKafkaCluster()
  {
    return $this->kafkaCluster;
  }
  /**
   * @param string[]
   */
  public function setLabels($labels)
  {
    $this->labels = $labels;
  }
  /**
   * @return string[]
   */
  public function getLabels()
  {
    return $this->labels;
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
class_alias(ConnectCluster::class, 'Google_Service_ManagedKafka_ConnectCluster');
