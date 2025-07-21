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

namespace Google\Service\Safebrowsing;

class GoogleSecuritySafebrowsingV5HashList extends \Google\Model
{
  protected $additionsEightBytesType = GoogleSecuritySafebrowsingV5RiceDeltaEncoded64Bit::class;
  protected $additionsEightBytesDataType = '';
  protected $additionsFourBytesType = GoogleSecuritySafebrowsingV5RiceDeltaEncoded32Bit::class;
  protected $additionsFourBytesDataType = '';
  protected $additionsSixteenBytesType = GoogleSecuritySafebrowsingV5RiceDeltaEncoded128Bit::class;
  protected $additionsSixteenBytesDataType = '';
  protected $additionsThirtyTwoBytesType = GoogleSecuritySafebrowsingV5RiceDeltaEncoded256Bit::class;
  protected $additionsThirtyTwoBytesDataType = '';
  protected $compressedRemovalsType = GoogleSecuritySafebrowsingV5RiceDeltaEncoded32Bit::class;
  protected $compressedRemovalsDataType = '';
  protected $metadataType = GoogleSecuritySafebrowsingV5HashListMetadata::class;
  protected $metadataDataType = '';
  /**
   * @var string
   */
  public $minimumWaitDuration;
  /**
   * @var string
   */
  public $name;
  /**
   * @var bool
   */
  public $partialUpdate;
  /**
   * @var string
   */
  public $sha256Checksum;
  /**
   * @var string
   */
  public $version;

  /**
   * @param GoogleSecuritySafebrowsingV5RiceDeltaEncoded64Bit
   */
  public function setAdditionsEightBytes(GoogleSecuritySafebrowsingV5RiceDeltaEncoded64Bit $additionsEightBytes)
  {
    $this->additionsEightBytes = $additionsEightBytes;
  }
  /**
   * @return GoogleSecuritySafebrowsingV5RiceDeltaEncoded64Bit
   */
  public function getAdditionsEightBytes()
  {
    return $this->additionsEightBytes;
  }
  /**
   * @param GoogleSecuritySafebrowsingV5RiceDeltaEncoded32Bit
   */
  public function setAdditionsFourBytes(GoogleSecuritySafebrowsingV5RiceDeltaEncoded32Bit $additionsFourBytes)
  {
    $this->additionsFourBytes = $additionsFourBytes;
  }
  /**
   * @return GoogleSecuritySafebrowsingV5RiceDeltaEncoded32Bit
   */
  public function getAdditionsFourBytes()
  {
    return $this->additionsFourBytes;
  }
  /**
   * @param GoogleSecuritySafebrowsingV5RiceDeltaEncoded128Bit
   */
  public function setAdditionsSixteenBytes(GoogleSecuritySafebrowsingV5RiceDeltaEncoded128Bit $additionsSixteenBytes)
  {
    $this->additionsSixteenBytes = $additionsSixteenBytes;
  }
  /**
   * @return GoogleSecuritySafebrowsingV5RiceDeltaEncoded128Bit
   */
  public function getAdditionsSixteenBytes()
  {
    return $this->additionsSixteenBytes;
  }
  /**
   * @param GoogleSecuritySafebrowsingV5RiceDeltaEncoded256Bit
   */
  public function setAdditionsThirtyTwoBytes(GoogleSecuritySafebrowsingV5RiceDeltaEncoded256Bit $additionsThirtyTwoBytes)
  {
    $this->additionsThirtyTwoBytes = $additionsThirtyTwoBytes;
  }
  /**
   * @return GoogleSecuritySafebrowsingV5RiceDeltaEncoded256Bit
   */
  public function getAdditionsThirtyTwoBytes()
  {
    return $this->additionsThirtyTwoBytes;
  }
  /**
   * @param GoogleSecuritySafebrowsingV5RiceDeltaEncoded32Bit
   */
  public function setCompressedRemovals(GoogleSecuritySafebrowsingV5RiceDeltaEncoded32Bit $compressedRemovals)
  {
    $this->compressedRemovals = $compressedRemovals;
  }
  /**
   * @return GoogleSecuritySafebrowsingV5RiceDeltaEncoded32Bit
   */
  public function getCompressedRemovals()
  {
    return $this->compressedRemovals;
  }
  /**
   * @param GoogleSecuritySafebrowsingV5HashListMetadata
   */
  public function setMetadata(GoogleSecuritySafebrowsingV5HashListMetadata $metadata)
  {
    $this->metadata = $metadata;
  }
  /**
   * @return GoogleSecuritySafebrowsingV5HashListMetadata
   */
  public function getMetadata()
  {
    return $this->metadata;
  }
  /**
   * @param string
   */
  public function setMinimumWaitDuration($minimumWaitDuration)
  {
    $this->minimumWaitDuration = $minimumWaitDuration;
  }
  /**
   * @return string
   */
  public function getMinimumWaitDuration()
  {
    return $this->minimumWaitDuration;
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
   * @param bool
   */
  public function setPartialUpdate($partialUpdate)
  {
    $this->partialUpdate = $partialUpdate;
  }
  /**
   * @return bool
   */
  public function getPartialUpdate()
  {
    return $this->partialUpdate;
  }
  /**
   * @param string
   */
  public function setSha256Checksum($sha256Checksum)
  {
    $this->sha256Checksum = $sha256Checksum;
  }
  /**
   * @return string
   */
  public function getSha256Checksum()
  {
    return $this->sha256Checksum;
  }
  /**
   * @param string
   */
  public function setVersion($version)
  {
    $this->version = $version;
  }
  /**
   * @return string
   */
  public function getVersion()
  {
    return $this->version;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleSecuritySafebrowsingV5HashList::class, 'Google_Service_Safebrowsing_GoogleSecuritySafebrowsingV5HashList');
