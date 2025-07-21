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

namespace Google\Service\StorageBatchOperations;

class Bucket extends \Google\Model
{
  /**
   * @var string
   */
  public $bucket;
  protected $manifestType = Manifest::class;
  protected $manifestDataType = '';
  protected $prefixListType = PrefixList::class;
  protected $prefixListDataType = '';

  /**
   * @param string
   */
  public function setBucket($bucket)
  {
    $this->bucket = $bucket;
  }
  /**
   * @return string
   */
  public function getBucket()
  {
    return $this->bucket;
  }
  /**
   * @param Manifest
   */
  public function setManifest(Manifest $manifest)
  {
    $this->manifest = $manifest;
  }
  /**
   * @return Manifest
   */
  public function getManifest()
  {
    return $this->manifest;
  }
  /**
   * @param PrefixList
   */
  public function setPrefixList(PrefixList $prefixList)
  {
    $this->prefixList = $prefixList;
  }
  /**
   * @return PrefixList
   */
  public function getPrefixList()
  {
    return $this->prefixList;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Bucket::class, 'Google_Service_StorageBatchOperations_Bucket');
