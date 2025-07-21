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

class Acl extends \Google\Collection
{
  protected $collection_key = 'aclEntries';
  protected $aclEntriesType = AclEntry::class;
  protected $aclEntriesDataType = 'array';
  /**
   * @var string
   */
  public $etag;
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $patternType;
  /**
   * @var string
   */
  public $resourceName;
  /**
   * @var string
   */
  public $resourceType;

  /**
   * @param AclEntry[]
   */
  public function setAclEntries($aclEntries)
  {
    $this->aclEntries = $aclEntries;
  }
  /**
   * @return AclEntry[]
   */
  public function getAclEntries()
  {
    return $this->aclEntries;
  }
  /**
   * @param string
   */
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  /**
   * @return string
   */
  public function getEtag()
  {
    return $this->etag;
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
  public function setPatternType($patternType)
  {
    $this->patternType = $patternType;
  }
  /**
   * @return string
   */
  public function getPatternType()
  {
    return $this->patternType;
  }
  /**
   * @param string
   */
  public function setResourceName($resourceName)
  {
    $this->resourceName = $resourceName;
  }
  /**
   * @return string
   */
  public function getResourceName()
  {
    return $this->resourceName;
  }
  /**
   * @param string
   */
  public function setResourceType($resourceType)
  {
    $this->resourceType = $resourceType;
  }
  /**
   * @return string
   */
  public function getResourceType()
  {
    return $this->resourceType;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Acl::class, 'Google_Service_ManagedKafka_Acl');
