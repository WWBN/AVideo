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

class ResearchVisionFace2cartoonFace2CartoonResults extends \Google\Collection
{
  protected $collection_key = 'skinToneClassifierResults';
  protected $ageClassifierResultsType = ResearchVisionFace2cartoonAgeClassifierResults::class;
  protected $ageClassifierResultsDataType = 'array';
  protected $chinLengthClassifierResultsType = ResearchVisionFace2cartoonChinLengthClassifierResults::class;
  protected $chinLengthClassifierResultsDataType = 'array';
  protected $eyeColorClassifierResultsType = ResearchVisionFace2cartoonEyeColorClassifierResults::class;
  protected $eyeColorClassifierResultsDataType = 'array';
  protected $eyeEyebrowDistanceClassifierResultsType = ResearchVisionFace2cartoonEyeEyebrowDistanceClassifierResults::class;
  protected $eyeEyebrowDistanceClassifierResultsDataType = 'array';
  protected $eyeShapeClassifierResultsType = ResearchVisionFace2cartoonEyeShapeClassifierResults::class;
  protected $eyeShapeClassifierResultsDataType = 'array';
  protected $eyeSlantClassifierResultsType = ResearchVisionFace2cartoonEyeSlantClassifierResults::class;
  protected $eyeSlantClassifierResultsDataType = 'array';
  protected $eyeVerticalPositionClassifierResultsType = ResearchVisionFace2cartoonEyeVerticalPositionClassifierResults::class;
  protected $eyeVerticalPositionClassifierResultsDataType = 'array';
  protected $eyebrowShapeClassifierResultsType = ResearchVisionFace2cartoonEyebrowShapeClassifierResults::class;
  protected $eyebrowShapeClassifierResultsDataType = 'array';
  protected $eyebrowThicknessClassifierResultsType = ResearchVisionFace2cartoonEyebrowThicknessClassifierResults::class;
  protected $eyebrowThicknessClassifierResultsDataType = 'array';
  protected $eyebrowWidthClassifierResultsType = ResearchVisionFace2cartoonEyebrowWidthClassifierResults::class;
  protected $eyebrowWidthClassifierResultsDataType = 'array';
  protected $faceWidthClassifierResultsType = ResearchVisionFace2cartoonFaceWidthClassifierResults::class;
  protected $faceWidthClassifierResultsDataType = 'array';
  protected $facialHairClassifierResultsType = ResearchVisionFace2cartoonFacialHairClassifierResults::class;
  protected $facialHairClassifierResultsDataType = 'array';
  protected $genderClassifierResultsType = ResearchVisionFace2cartoonGenderClassifierResults::class;
  protected $genderClassifierResultsDataType = 'array';
  protected $glassesClassifierResultsType = ResearchVisionFace2cartoonGlassesClassifierResults::class;
  protected $glassesClassifierResultsDataType = 'array';
  protected $hairColorClassifierResultsType = ResearchVisionFace2cartoonHairColorClassifierResults::class;
  protected $hairColorClassifierResultsDataType = 'array';
  protected $hairStyleClassifierResultsType = ResearchVisionFace2cartoonHairStyleClassifierResults::class;
  protected $hairStyleClassifierResultsDataType = 'array';
  protected $interEyeDistanceClassifierResultsType = ResearchVisionFace2cartoonInterEyeDistanceClassifierResults::class;
  protected $interEyeDistanceClassifierResultsDataType = 'array';
  protected $jawShapeClassifierResultsType = ResearchVisionFace2cartoonJawShapeClassifierResults::class;
  protected $jawShapeClassifierResultsDataType = 'array';
  protected $lipThicknessClassifierResultsType = ResearchVisionFace2cartoonLipThicknessClassifierResults::class;
  protected $lipThicknessClassifierResultsDataType = 'array';
  protected $mouthVerticalPositionClassifierResultsType = ResearchVisionFace2cartoonMouthVerticalPositionClassifierResults::class;
  protected $mouthVerticalPositionClassifierResultsDataType = 'array';
  protected $mouthWidthClassifierResultsType = ResearchVisionFace2cartoonMouthWidthClassifierResults::class;
  protected $mouthWidthClassifierResultsDataType = 'array';
  protected $noseVerticalPositionClassifierResultsType = ResearchVisionFace2cartoonNoseVerticalPositionClassifierResults::class;
  protected $noseVerticalPositionClassifierResultsDataType = 'array';
  protected $noseWidthClassifierResultsType = ResearchVisionFace2cartoonNoseWidthClassifierResults::class;
  protected $noseWidthClassifierResultsDataType = 'array';
  protected $skinToneClassifierResultsType = ResearchVisionFace2cartoonSkinToneClassifierResults::class;
  protected $skinToneClassifierResultsDataType = 'array';

  /**
   * @param ResearchVisionFace2cartoonAgeClassifierResults[]
   */
  public function setAgeClassifierResults($ageClassifierResults)
  {
    $this->ageClassifierResults = $ageClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonAgeClassifierResults[]
   */
  public function getAgeClassifierResults()
  {
    return $this->ageClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonChinLengthClassifierResults[]
   */
  public function setChinLengthClassifierResults($chinLengthClassifierResults)
  {
    $this->chinLengthClassifierResults = $chinLengthClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonChinLengthClassifierResults[]
   */
  public function getChinLengthClassifierResults()
  {
    return $this->chinLengthClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonEyeColorClassifierResults[]
   */
  public function setEyeColorClassifierResults($eyeColorClassifierResults)
  {
    $this->eyeColorClassifierResults = $eyeColorClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonEyeColorClassifierResults[]
   */
  public function getEyeColorClassifierResults()
  {
    return $this->eyeColorClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonEyeEyebrowDistanceClassifierResults[]
   */
  public function setEyeEyebrowDistanceClassifierResults($eyeEyebrowDistanceClassifierResults)
  {
    $this->eyeEyebrowDistanceClassifierResults = $eyeEyebrowDistanceClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonEyeEyebrowDistanceClassifierResults[]
   */
  public function getEyeEyebrowDistanceClassifierResults()
  {
    return $this->eyeEyebrowDistanceClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonEyeShapeClassifierResults[]
   */
  public function setEyeShapeClassifierResults($eyeShapeClassifierResults)
  {
    $this->eyeShapeClassifierResults = $eyeShapeClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonEyeShapeClassifierResults[]
   */
  public function getEyeShapeClassifierResults()
  {
    return $this->eyeShapeClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonEyeSlantClassifierResults[]
   */
  public function setEyeSlantClassifierResults($eyeSlantClassifierResults)
  {
    $this->eyeSlantClassifierResults = $eyeSlantClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonEyeSlantClassifierResults[]
   */
  public function getEyeSlantClassifierResults()
  {
    return $this->eyeSlantClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonEyeVerticalPositionClassifierResults[]
   */
  public function setEyeVerticalPositionClassifierResults($eyeVerticalPositionClassifierResults)
  {
    $this->eyeVerticalPositionClassifierResults = $eyeVerticalPositionClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonEyeVerticalPositionClassifierResults[]
   */
  public function getEyeVerticalPositionClassifierResults()
  {
    return $this->eyeVerticalPositionClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonEyebrowShapeClassifierResults[]
   */
  public function setEyebrowShapeClassifierResults($eyebrowShapeClassifierResults)
  {
    $this->eyebrowShapeClassifierResults = $eyebrowShapeClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonEyebrowShapeClassifierResults[]
   */
  public function getEyebrowShapeClassifierResults()
  {
    return $this->eyebrowShapeClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonEyebrowThicknessClassifierResults[]
   */
  public function setEyebrowThicknessClassifierResults($eyebrowThicknessClassifierResults)
  {
    $this->eyebrowThicknessClassifierResults = $eyebrowThicknessClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonEyebrowThicknessClassifierResults[]
   */
  public function getEyebrowThicknessClassifierResults()
  {
    return $this->eyebrowThicknessClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonEyebrowWidthClassifierResults[]
   */
  public function setEyebrowWidthClassifierResults($eyebrowWidthClassifierResults)
  {
    $this->eyebrowWidthClassifierResults = $eyebrowWidthClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonEyebrowWidthClassifierResults[]
   */
  public function getEyebrowWidthClassifierResults()
  {
    return $this->eyebrowWidthClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonFaceWidthClassifierResults[]
   */
  public function setFaceWidthClassifierResults($faceWidthClassifierResults)
  {
    $this->faceWidthClassifierResults = $faceWidthClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonFaceWidthClassifierResults[]
   */
  public function getFaceWidthClassifierResults()
  {
    return $this->faceWidthClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonFacialHairClassifierResults[]
   */
  public function setFacialHairClassifierResults($facialHairClassifierResults)
  {
    $this->facialHairClassifierResults = $facialHairClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonFacialHairClassifierResults[]
   */
  public function getFacialHairClassifierResults()
  {
    return $this->facialHairClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonGenderClassifierResults[]
   */
  public function setGenderClassifierResults($genderClassifierResults)
  {
    $this->genderClassifierResults = $genderClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonGenderClassifierResults[]
   */
  public function getGenderClassifierResults()
  {
    return $this->genderClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonGlassesClassifierResults[]
   */
  public function setGlassesClassifierResults($glassesClassifierResults)
  {
    $this->glassesClassifierResults = $glassesClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonGlassesClassifierResults[]
   */
  public function getGlassesClassifierResults()
  {
    return $this->glassesClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonHairColorClassifierResults[]
   */
  public function setHairColorClassifierResults($hairColorClassifierResults)
  {
    $this->hairColorClassifierResults = $hairColorClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonHairColorClassifierResults[]
   */
  public function getHairColorClassifierResults()
  {
    return $this->hairColorClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonHairStyleClassifierResults[]
   */
  public function setHairStyleClassifierResults($hairStyleClassifierResults)
  {
    $this->hairStyleClassifierResults = $hairStyleClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonHairStyleClassifierResults[]
   */
  public function getHairStyleClassifierResults()
  {
    return $this->hairStyleClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonInterEyeDistanceClassifierResults[]
   */
  public function setInterEyeDistanceClassifierResults($interEyeDistanceClassifierResults)
  {
    $this->interEyeDistanceClassifierResults = $interEyeDistanceClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonInterEyeDistanceClassifierResults[]
   */
  public function getInterEyeDistanceClassifierResults()
  {
    return $this->interEyeDistanceClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonJawShapeClassifierResults[]
   */
  public function setJawShapeClassifierResults($jawShapeClassifierResults)
  {
    $this->jawShapeClassifierResults = $jawShapeClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonJawShapeClassifierResults[]
   */
  public function getJawShapeClassifierResults()
  {
    return $this->jawShapeClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonLipThicknessClassifierResults[]
   */
  public function setLipThicknessClassifierResults($lipThicknessClassifierResults)
  {
    $this->lipThicknessClassifierResults = $lipThicknessClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonLipThicknessClassifierResults[]
   */
  public function getLipThicknessClassifierResults()
  {
    return $this->lipThicknessClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonMouthVerticalPositionClassifierResults[]
   */
  public function setMouthVerticalPositionClassifierResults($mouthVerticalPositionClassifierResults)
  {
    $this->mouthVerticalPositionClassifierResults = $mouthVerticalPositionClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonMouthVerticalPositionClassifierResults[]
   */
  public function getMouthVerticalPositionClassifierResults()
  {
    return $this->mouthVerticalPositionClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonMouthWidthClassifierResults[]
   */
  public function setMouthWidthClassifierResults($mouthWidthClassifierResults)
  {
    $this->mouthWidthClassifierResults = $mouthWidthClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonMouthWidthClassifierResults[]
   */
  public function getMouthWidthClassifierResults()
  {
    return $this->mouthWidthClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonNoseVerticalPositionClassifierResults[]
   */
  public function setNoseVerticalPositionClassifierResults($noseVerticalPositionClassifierResults)
  {
    $this->noseVerticalPositionClassifierResults = $noseVerticalPositionClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonNoseVerticalPositionClassifierResults[]
   */
  public function getNoseVerticalPositionClassifierResults()
  {
    return $this->noseVerticalPositionClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonNoseWidthClassifierResults[]
   */
  public function setNoseWidthClassifierResults($noseWidthClassifierResults)
  {
    $this->noseWidthClassifierResults = $noseWidthClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonNoseWidthClassifierResults[]
   */
  public function getNoseWidthClassifierResults()
  {
    return $this->noseWidthClassifierResults;
  }
  /**
   * @param ResearchVisionFace2cartoonSkinToneClassifierResults[]
   */
  public function setSkinToneClassifierResults($skinToneClassifierResults)
  {
    $this->skinToneClassifierResults = $skinToneClassifierResults;
  }
  /**
   * @return ResearchVisionFace2cartoonSkinToneClassifierResults[]
   */
  public function getSkinToneClassifierResults()
  {
    return $this->skinToneClassifierResults;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ResearchVisionFace2cartoonFace2CartoonResults::class, 'Google_Service_Contentwarehouse_ResearchVisionFace2cartoonFace2CartoonResults');
