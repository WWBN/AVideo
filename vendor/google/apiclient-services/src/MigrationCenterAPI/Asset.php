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

namespace Google\Service\MigrationCenterAPI;

class Asset extends \Google\Collection
{
  protected $collection_key = 'sources';
  /**
   * @var string[]
   */
  public $assignedGroups;
  /**
   * @var string[]
   */
  public $attributes;
  /**
   * @var string
   */
  public $createTime;
  protected $insightListType = InsightList::class;
  protected $insightListDataType = '';
  /**
   * @var string[]
   */
  public $labels;
  /**
   * @var string
   */
  public $name;
  protected $performanceDataType = AssetPerformanceData::class;
  protected $performanceDataDataType = '';
  /**
   * @var string[]
   */
  public $sources;
  /**
   * @var string
   */
  public $updateTime;
  protected $virtualMachineDetailsType = VirtualMachineDetails::class;
  protected $virtualMachineDetailsDataType = '';

  /**
   * @param string[]
   */
  public function setAssignedGroups($assignedGroups)
  {
    $this->assignedGroups = $assignedGroups;
  }
  /**
   * @return string[]
   */
  public function getAssignedGroups()
  {
    return $this->assignedGroups;
  }
  /**
   * @param string[]
   */
  public function setAttributes($attributes)
  {
    $this->attributes = $attributes;
  }
  /**
   * @return string[]
   */
  public function getAttributes()
  {
    return $this->attributes;
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
   * @param InsightList
   */
  public function setInsightList(InsightList $insightList)
  {
    $this->insightList = $insightList;
  }
  /**
   * @return InsightList
   */
  public function getInsightList()
  {
    return $this->insightList;
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
   * @param AssetPerformanceData
   */
  public function setPerformanceData(AssetPerformanceData $performanceData)
  {
    $this->performanceData = $performanceData;
  }
  /**
   * @return AssetPerformanceData
   */
  public function getPerformanceData()
  {
    return $this->performanceData;
  }
  /**
   * @param string[]
   */
  public function setSources($sources)
  {
    $this->sources = $sources;
  }
  /**
   * @return string[]
   */
  public function getSources()
  {
    return $this->sources;
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
  /**
   * @param VirtualMachineDetails
   */
  public function setVirtualMachineDetails(VirtualMachineDetails $virtualMachineDetails)
  {
    $this->virtualMachineDetails = $virtualMachineDetails;
  }
  /**
   * @return VirtualMachineDetails
   */
  public function getVirtualMachineDetails()
  {
    return $this->virtualMachineDetails;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Asset::class, 'Google_Service_MigrationCenterAPI_Asset');
