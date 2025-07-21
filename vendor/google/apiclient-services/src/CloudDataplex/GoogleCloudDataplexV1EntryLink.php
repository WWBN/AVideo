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

namespace Google\Service\CloudDataplex;

class GoogleCloudDataplexV1EntryLink extends \Google\Collection
{
  protected $collection_key = 'entryReferences';
  /**
   * @var string
   */
  public $createTime;
  /**
   * @var string
   */
  public $entryLinkType;
  protected $entryReferencesType = GoogleCloudDataplexV1EntryLinkEntryReference::class;
  protected $entryReferencesDataType = 'array';
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $updateTime;

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
  public function setEntryLinkType($entryLinkType)
  {
    $this->entryLinkType = $entryLinkType;
  }
  /**
   * @return string
   */
  public function getEntryLinkType()
  {
    return $this->entryLinkType;
  }
  /**
   * @param GoogleCloudDataplexV1EntryLinkEntryReference[]
   */
  public function setEntryReferences($entryReferences)
  {
    $this->entryReferences = $entryReferences;
  }
  /**
   * @return GoogleCloudDataplexV1EntryLinkEntryReference[]
   */
  public function getEntryReferences()
  {
    return $this->entryReferences;
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
class_alias(GoogleCloudDataplexV1EntryLink::class, 'Google_Service_CloudDataplex_GoogleCloudDataplexV1EntryLink');
