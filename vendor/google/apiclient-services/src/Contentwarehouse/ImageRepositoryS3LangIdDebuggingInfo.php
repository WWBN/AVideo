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

class ImageRepositoryS3LangIdDebuggingInfo extends \Google\Model
{
  /**
   * @var string
   */
  public $audioInputCap;
  /**
   * @var int
   */
  public $failedSegments;
  /**
   * @var int
   */
  public $processedSegments;
  /**
   * @var string
   */
  public $segmentDuration;
  /**
   * @var int
   */
  public $segmentStride;
  protected $waveHeaderType = SpeechWaveHeader::class;
  protected $waveHeaderDataType = '';

  /**
   * @param string
   */
  public function setAudioInputCap($audioInputCap)
  {
    $this->audioInputCap = $audioInputCap;
  }
  /**
   * @return string
   */
  public function getAudioInputCap()
  {
    return $this->audioInputCap;
  }
  /**
   * @param int
   */
  public function setFailedSegments($failedSegments)
  {
    $this->failedSegments = $failedSegments;
  }
  /**
   * @return int
   */
  public function getFailedSegments()
  {
    return $this->failedSegments;
  }
  /**
   * @param int
   */
  public function setProcessedSegments($processedSegments)
  {
    $this->processedSegments = $processedSegments;
  }
  /**
   * @return int
   */
  public function getProcessedSegments()
  {
    return $this->processedSegments;
  }
  /**
   * @param string
   */
  public function setSegmentDuration($segmentDuration)
  {
    $this->segmentDuration = $segmentDuration;
  }
  /**
   * @return string
   */
  public function getSegmentDuration()
  {
    return $this->segmentDuration;
  }
  /**
   * @param int
   */
  public function setSegmentStride($segmentStride)
  {
    $this->segmentStride = $segmentStride;
  }
  /**
   * @return int
   */
  public function getSegmentStride()
  {
    return $this->segmentStride;
  }
  /**
   * @param SpeechWaveHeader
   */
  public function setWaveHeader(SpeechWaveHeader $waveHeader)
  {
    $this->waveHeader = $waveHeader;
  }
  /**
   * @return SpeechWaveHeader
   */
  public function getWaveHeader()
  {
    return $this->waveHeader;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ImageRepositoryS3LangIdDebuggingInfo::class, 'Google_Service_Contentwarehouse_ImageRepositoryS3LangIdDebuggingInfo');
