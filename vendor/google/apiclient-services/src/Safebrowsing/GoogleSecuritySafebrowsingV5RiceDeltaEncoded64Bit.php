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

class GoogleSecuritySafebrowsingV5RiceDeltaEncoded64Bit extends \Google\Model
{
  /**
   * @var string
   */
  public $encodedData;
  /**
   * @var int
   */
  public $entriesCount;
  /**
   * @var string
   */
  public $firstValue;
  /**
   * @var int
   */
  public $riceParameter;

  /**
   * @param string
   */
  public function setEncodedData($encodedData)
  {
    $this->encodedData = $encodedData;
  }
  /**
   * @return string
   */
  public function getEncodedData()
  {
    return $this->encodedData;
  }
  /**
   * @param int
   */
  public function setEntriesCount($entriesCount)
  {
    $this->entriesCount = $entriesCount;
  }
  /**
   * @return int
   */
  public function getEntriesCount()
  {
    return $this->entriesCount;
  }
  /**
   * @param string
   */
  public function setFirstValue($firstValue)
  {
    $this->firstValue = $firstValue;
  }
  /**
   * @return string
   */
  public function getFirstValue()
  {
    return $this->firstValue;
  }
  /**
   * @param int
   */
  public function setRiceParameter($riceParameter)
  {
    $this->riceParameter = $riceParameter;
  }
  /**
   * @return int
   */
  public function getRiceParameter()
  {
    return $this->riceParameter;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleSecuritySafebrowsingV5RiceDeltaEncoded64Bit::class, 'Google_Service_Safebrowsing_GoogleSecuritySafebrowsingV5RiceDeltaEncoded64Bit');
