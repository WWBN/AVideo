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

namespace Google\Service\Contentwarehouse;

class SpeechWaveHeader extends \Google\Collection
{
  protected $collection_key = 'dimension';
  /**
   * @var int
   */
  public $atomicSize;
  /**
   * @var string
   */
  public $atomicType;
  /**
   * @var float
   */
  public $bitRate;
  /**
   * @var string
   */
  public $byteOrder;
  /**
   * @var string
   */
  public $details;
  /**
   * @var int[]
   */
  public $dimension;
  /**
   * @var int
   */
  public $elementsPerSample;
  /**
   * @var int
   */
  public $rank;
  /**
   * @var string
   */
  public $sampleCoding;
  /**
   * @var float
   */
  public $sampleRate;
  /**
   * @var int
   */
  public $sampleSize;
  /**
   * @var string
   */
  public $sampleType;
  /**
   * @var float
   */
  public $startTime;
  /**
   * @var string
   */
  public $totalSamples;

  /**
   * @param int
   */
  public function setAtomicSize($atomicSize)
  {
    $this->atomicSize = $atomicSize;
  }
  /**
   * @return int
   */
  public function getAtomicSize()
  {
    return $this->atomicSize;
  }
  /**
   * @param string
   */
  public function setAtomicType($atomicType)
  {
    $this->atomicType = $atomicType;
  }
  /**
   * @return string
   */
  public function getAtomicType()
  {
    return $this->atomicType;
  }
  /**
   * @param float
   */
  public function setBitRate($bitRate)
  {
    $this->bitRate = $bitRate;
  }
  /**
   * @return float
   */
  public function getBitRate()
  {
    return $this->bitRate;
  }
  /**
   * @param string
   */
  public function setByteOrder($byteOrder)
  {
    $this->byteOrder = $byteOrder;
  }
  /**
   * @return string
   */
  public function getByteOrder()
  {
    return $this->byteOrder;
  }
  /**
   * @param string
   */
  public function setDetails($details)
  {
    $this->details = $details;
  }
  /**
   * @return string
   */
  public function getDetails()
  {
    return $this->details;
  }
  /**
   * @param int[]
   */
  public function setDimension($dimension)
  {
    $this->dimension = $dimension;
  }
  /**
   * @return int[]
   */
  public function getDimension()
  {
    return $this->dimension;
  }
  /**
   * @param int
   */
  public function setElementsPerSample($elementsPerSample)
  {
    $this->elementsPerSample = $elementsPerSample;
  }
  /**
   * @return int
   */
  public function getElementsPerSample()
  {
    return $this->elementsPerSample;
  }
  /**
   * @param int
   */
  public function setRank($rank)
  {
    $this->rank = $rank;
  }
  /**
   * @return int
   */
  public function getRank()
  {
    return $this->rank;
  }
  /**
   * @param string
   */
  public function setSampleCoding($sampleCoding)
  {
    $this->sampleCoding = $sampleCoding;
  }
  /**
   * @return string
   */
  public function getSampleCoding()
  {
    return $this->sampleCoding;
  }
  /**
   * @param float
   */
  public function setSampleRate($sampleRate)
  {
    $this->sampleRate = $sampleRate;
  }
  /**
   * @return float
   */
  public function getSampleRate()
  {
    return $this->sampleRate;
  }
  /**
   * @param int
   */
  public function setSampleSize($sampleSize)
  {
    $this->sampleSize = $sampleSize;
  }
  /**
   * @return int
   */
  public function getSampleSize()
  {
    return $this->sampleSize;
  }
  /**
   * @param string
   */
  public function setSampleType($sampleType)
  {
    $this->sampleType = $sampleType;
  }
  /**
   * @return string
   */
  public function getSampleType()
  {
    return $this->sampleType;
  }
  /**
   * @param float
   */
  public function setStartTime($startTime)
  {
    $this->startTime = $startTime;
  }
  /**
   * @return float
   */
  public function getStartTime()
  {
    return $this->startTime;
  }
  /**
   * @param string
   */
  public function setTotalSamples($totalSamples)
  {
    $this->totalSamples = $totalSamples;
  }
  /**
   * @return string
   */
  public function getTotalSamples()
  {
    return $this->totalSamples;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(SpeechWaveHeader::class, 'Google_Service_Contentwarehouse_SpeechWaveHeader');
