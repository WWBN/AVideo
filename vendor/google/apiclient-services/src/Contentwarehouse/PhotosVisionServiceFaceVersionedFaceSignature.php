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

class PhotosVisionServiceFaceVersionedFaceSignature extends \Google\Model
{
  /**
   * @var float
   */
  public $confidence;
  /**
   * @var string
   */
  public $confidenceVersion;
  /**
   * @var string
   */
  public $converterVersion;
  /**
   * @var string
   */
  public $signature;
  /**
   * @var string
   */
  public $signatureSource;
  /**
   * @var string
   */
  public $version;

  /**
   * @param float
   */
  public function setConfidence($confidence)
  {
    $this->confidence = $confidence;
  }
  /**
   * @return float
   */
  public function getConfidence()
  {
    return $this->confidence;
  }
  /**
   * @param string
   */
  public function setConfidenceVersion($confidenceVersion)
  {
    $this->confidenceVersion = $confidenceVersion;
  }
  /**
   * @return string
   */
  public function getConfidenceVersion()
  {
    return $this->confidenceVersion;
  }
  /**
   * @param string
   */
  public function setConverterVersion($converterVersion)
  {
    $this->converterVersion = $converterVersion;
  }
  /**
   * @return string
   */
  public function getConverterVersion()
  {
    return $this->converterVersion;
  }
  /**
   * @param string
   */
  public function setSignature($signature)
  {
    $this->signature = $signature;
  }
  /**
   * @return string
   */
  public function getSignature()
  {
    return $this->signature;
  }
  /**
   * @param string
   */
  public function setSignatureSource($signatureSource)
  {
    $this->signatureSource = $signatureSource;
  }
  /**
   * @return string
   */
  public function getSignatureSource()
  {
    return $this->signatureSource;
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
class_alias(PhotosVisionServiceFaceVersionedFaceSignature::class, 'Google_Service_Contentwarehouse_PhotosVisionServiceFaceVersionedFaceSignature');
