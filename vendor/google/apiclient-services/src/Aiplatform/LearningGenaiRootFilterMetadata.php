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

namespace Google\Service\Aiplatform;

class LearningGenaiRootFilterMetadata extends \Google\Model
{
  /**
   * @var string
   */
  public $confidence;
  protected $debugInfoType = LearningGenaiRootFilterMetadataFilterDebugInfo::class;
  protected $debugInfoDataType = '';
  /**
   * @var string
   */
  public $fallback;
  /**
   * @var string
   */
  public $info;
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $reason;
  /**
   * @var string
   */
  public $text;

  /**
   * @param string
   */
  public function setConfidence($confidence)
  {
    $this->confidence = $confidence;
  }
  /**
   * @return string
   */
  public function getConfidence()
  {
    return $this->confidence;
  }
  /**
   * @param LearningGenaiRootFilterMetadataFilterDebugInfo
   */
  public function setDebugInfo(LearningGenaiRootFilterMetadataFilterDebugInfo $debugInfo)
  {
    $this->debugInfo = $debugInfo;
  }
  /**
   * @return LearningGenaiRootFilterMetadataFilterDebugInfo
   */
  public function getDebugInfo()
  {
    return $this->debugInfo;
  }
  /**
   * @param string
   */
  public function setFallback($fallback)
  {
    $this->fallback = $fallback;
  }
  /**
   * @return string
   */
  public function getFallback()
  {
    return $this->fallback;
  }
  /**
   * @param string
   */
  public function setInfo($info)
  {
    $this->info = $info;
  }
  /**
   * @return string
   */
  public function getInfo()
  {
    return $this->info;
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
  public function setReason($reason)
  {
    $this->reason = $reason;
  }
  /**
   * @return string
   */
  public function getReason()
  {
    return $this->reason;
  }
  /**
   * @param string
   */
  public function setText($text)
  {
    $this->text = $text;
  }
  /**
   * @return string
   */
  public function getText()
  {
    return $this->text;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootFilterMetadata::class, 'Google_Service_Aiplatform_LearningGenaiRootFilterMetadata');
