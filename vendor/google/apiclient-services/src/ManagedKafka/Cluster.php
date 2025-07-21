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

class Cluster extends \Google\Model
{
  protected $capacityConfigType = CapacityConfig::class;
  protected $capacityConfigDataType = '';
  /**
   * @var string
   */
  public $createTime;
  protected $gcpConfigType = GcpConfig::class;
  protected $gcpConfigDataType = '';
  /**
   * @var string[]
   */
  public $labels;
  /**
   * @var string
   */
  public $name;
  protected $rebalanceConfigType = RebalanceConfig::class;
  protected $rebalanceConfigDataType = '';
  /**
   * @var bool
   */
  public $satisfiesPzi;
  /**
   * @var bool
   */
  public $satisfiesPzs;
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
   * @param GcpConfig
   */
  public function setGcpConfig(GcpConfig $gcpConfig)
  {
    $this->gcpConfig = $gcpConfig;
  }
  /**
   * @return GcpConfig
   */
  public function getGcpConfig()
  {
    return $this->gcpConfig;
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
   * @param RebalanceConfig
   */
  public function setRebalanceConfig(RebalanceConfig $rebalanceConfig)
  {
    $this->rebalanceConfig = $rebalanceConfig;
  }
  /**
   * @return RebalanceConfig
   */
  public function getRebalanceConfig()
  {
    return $this->rebalanceConfig;
  }
  /**
   * @param bool
   */
  public function setSatisfiesPzi($satisfiesPzi)
  {
    $this->satisfiesPzi = $satisfiesPzi;
  }
  /**
   * @return bool
   */
  public function getSatisfiesPzi()
  {
    return $this->satisfiesPzi;
  }
  /**
   * @param bool
   */
  public function setSatisfiesPzs($satisfiesPzs)
  {
    $this->satisfiesPzs = $satisfiesPzs;
  }
  /**
   * @return bool
   */
  public function getSatisfiesPzs()
  {
    return $this->satisfiesPzs;
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
class_alias(Cluster::class, 'Google_Service_ManagedKafka_Cluster');
