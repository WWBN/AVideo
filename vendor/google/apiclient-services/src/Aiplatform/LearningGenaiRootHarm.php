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

class LearningGenaiRootHarm extends \Google\Model
{
  /**
   * @var bool
   */
  public $contextualDangerous;
  /**
   * @var bool
   */
  public $csam;
  /**
   * @var bool
   */
  public $fringe;
  protected $grailImageHarmTypeType = LearningGenaiRootHarmGrailImageHarmType::class;
  protected $grailImageHarmTypeDataType = '';
  protected $grailTextHarmTypeType = LearningGenaiRootHarmGrailTextHarmType::class;
  protected $grailTextHarmTypeDataType = '';
  /**
   * @var bool
   */
  public $imageCsam;
  /**
   * @var bool
   */
  public $imagePedo;
  /**
   * @var bool
   */
  public $imagePorn;
  /**
   * @var bool
   */
  public $imageViolence;
  /**
   * @var bool
   */
  public $pqc;
  protected $safetycatType = LearningGenaiRootHarmSafetyCatCategories::class;
  protected $safetycatDataType = '';
  protected $spiiType = LearningGenaiRootHarmSpiiFilter::class;
  protected $spiiDataType = '';
  public $threshold;
  /**
   * @var bool
   */
  public $videoFrameCsam;
  /**
   * @var bool
   */
  public $videoFramePedo;
  /**
   * @var bool
   */
  public $videoFramePorn;
  /**
   * @var bool
   */
  public $videoFrameViolence;

  /**
   * @param bool
   */
  public function setContextualDangerous($contextualDangerous)
  {
    $this->contextualDangerous = $contextualDangerous;
  }
  /**
   * @return bool
   */
  public function getContextualDangerous()
  {
    return $this->contextualDangerous;
  }
  /**
   * @param bool
   */
  public function setCsam($csam)
  {
    $this->csam = $csam;
  }
  /**
   * @return bool
   */
  public function getCsam()
  {
    return $this->csam;
  }
  /**
   * @param bool
   */
  public function setFringe($fringe)
  {
    $this->fringe = $fringe;
  }
  /**
   * @return bool
   */
  public function getFringe()
  {
    return $this->fringe;
  }
  /**
   * @param LearningGenaiRootHarmGrailImageHarmType
   */
  public function setGrailImageHarmType(LearningGenaiRootHarmGrailImageHarmType $grailImageHarmType)
  {
    $this->grailImageHarmType = $grailImageHarmType;
  }
  /**
   * @return LearningGenaiRootHarmGrailImageHarmType
   */
  public function getGrailImageHarmType()
  {
    return $this->grailImageHarmType;
  }
  /**
   * @param LearningGenaiRootHarmGrailTextHarmType
   */
  public function setGrailTextHarmType(LearningGenaiRootHarmGrailTextHarmType $grailTextHarmType)
  {
    $this->grailTextHarmType = $grailTextHarmType;
  }
  /**
   * @return LearningGenaiRootHarmGrailTextHarmType
   */
  public function getGrailTextHarmType()
  {
    return $this->grailTextHarmType;
  }
  /**
   * @param bool
   */
  public function setImageCsam($imageCsam)
  {
    $this->imageCsam = $imageCsam;
  }
  /**
   * @return bool
   */
  public function getImageCsam()
  {
    return $this->imageCsam;
  }
  /**
   * @param bool
   */
  public function setImagePedo($imagePedo)
  {
    $this->imagePedo = $imagePedo;
  }
  /**
   * @return bool
   */
  public function getImagePedo()
  {
    return $this->imagePedo;
  }
  /**
   * @param bool
   */
  public function setImagePorn($imagePorn)
  {
    $this->imagePorn = $imagePorn;
  }
  /**
   * @return bool
   */
  public function getImagePorn()
  {
    return $this->imagePorn;
  }
  /**
   * @param bool
   */
  public function setImageViolence($imageViolence)
  {
    $this->imageViolence = $imageViolence;
  }
  /**
   * @return bool
   */
  public function getImageViolence()
  {
    return $this->imageViolence;
  }
  /**
   * @param bool
   */
  public function setPqc($pqc)
  {
    $this->pqc = $pqc;
  }
  /**
   * @return bool
   */
  public function getPqc()
  {
    return $this->pqc;
  }
  /**
   * @param LearningGenaiRootHarmSafetyCatCategories
   */
  public function setSafetycat(LearningGenaiRootHarmSafetyCatCategories $safetycat)
  {
    $this->safetycat = $safetycat;
  }
  /**
   * @return LearningGenaiRootHarmSafetyCatCategories
   */
  public function getSafetycat()
  {
    return $this->safetycat;
  }
  /**
   * @param LearningGenaiRootHarmSpiiFilter
   */
  public function setSpii(LearningGenaiRootHarmSpiiFilter $spii)
  {
    $this->spii = $spii;
  }
  /**
   * @return LearningGenaiRootHarmSpiiFilter
   */
  public function getSpii()
  {
    return $this->spii;
  }
  public function setThreshold($threshold)
  {
    $this->threshold = $threshold;
  }
  public function getThreshold()
  {
    return $this->threshold;
  }
  /**
   * @param bool
   */
  public function setVideoFrameCsam($videoFrameCsam)
  {
    $this->videoFrameCsam = $videoFrameCsam;
  }
  /**
   * @return bool
   */
  public function getVideoFrameCsam()
  {
    return $this->videoFrameCsam;
  }
  /**
   * @param bool
   */
  public function setVideoFramePedo($videoFramePedo)
  {
    $this->videoFramePedo = $videoFramePedo;
  }
  /**
   * @return bool
   */
  public function getVideoFramePedo()
  {
    return $this->videoFramePedo;
  }
  /**
   * @param bool
   */
  public function setVideoFramePorn($videoFramePorn)
  {
    $this->videoFramePorn = $videoFramePorn;
  }
  /**
   * @return bool
   */
  public function getVideoFramePorn()
  {
    return $this->videoFramePorn;
  }
  /**
   * @param bool
   */
  public function setVideoFrameViolence($videoFrameViolence)
  {
    $this->videoFrameViolence = $videoFrameViolence;
  }
  /**
   * @return bool
   */
  public function getVideoFrameViolence()
  {
    return $this->videoFrameViolence;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootHarm::class, 'Google_Service_Aiplatform_LearningGenaiRootHarm');
