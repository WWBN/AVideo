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

class PhotosVisionServiceFaceFaceParams extends \Google\Collection
{
  protected $collection_key = 'versionedSignatures';
  /**
   * @var float
   */
  public $age;
  /**
   * @var float
   */
  public $angerProbability;
  protected $attributeType = HumanSensingFaceAttribute::class;
  protected $attributeDataType = 'array';
  /**
   * @var float
   */
  public $beardProbability;
  /**
   * @var float
   */
  public $blurredProbability;
  protected $boundingBoxType = PhotosVisionServiceFaceFaceParamsBoundingBox::class;
  protected $boundingBoxDataType = '';
  /**
   * @var float
   */
  public $darkGlassesProbability;
  /**
   * @var float
   */
  public $detectionConfidence;
  protected $extendedLandmarksType = PhotosVisionServiceFaceFaceParamsExtendedLandmark::class;
  protected $extendedLandmarksDataType = 'array';
  /**
   * @var float
   */
  public $eyesClosedProbability;
  protected $face2cartoonResultsType = ResearchVisionFace2cartoonFace2CartoonResults::class;
  protected $face2cartoonResultsDataType = '';
  protected $faceCropV8Type = PhotosVisionServiceFaceFaceParamsFaceCropV8::class;
  protected $faceCropV8DataType = '';
  protected $fdBoundingBoxType = PhotosVisionServiceFaceFaceParamsBoundingBox::class;
  protected $fdBoundingBoxDataType = '';
  /**
   * @var float
   */
  public $femaleProbability;
  /**
   * @var float
   */
  public $frontalGazeProbability;
  /**
   * @var float
   */
  public $glassesProbability;
  /**
   * @var float
   */
  public $headwearProbability;
  protected $imageParamsType = PhotosVisionServiceFaceImageParams::class;
  protected $imageParamsDataType = '';
  /**
   * @var float
   */
  public $joyProbability;
  protected $landmarkPositionsType = PhotosVisionServiceFaceFaceParamsLandmarkPosition::class;
  protected $landmarkPositionsDataType = 'array';
  /**
   * @var float
   */
  public $landmarkingConfidence;
  /**
   * @var float
   */
  public $leftEyeClosedProbability;
  /**
   * @var float
   */
  public $longHairProbability;
  /**
   * @var float
   */
  public $mouthOpenProbability;
  /**
   * @var float
   */
  public $nonHumanProbability;
  /**
   * @var float
   */
  public $panAngle;
  protected $poseMatrixType = PhotosVisionServiceFaceFaceParamsPoseMatrix::class;
  protected $poseMatrixDataType = '';
  /**
   * @var string
   */
  public $pretemplate;
  /**
   * @var float
   */
  public $qualityScore;
  /**
   * @var float
   */
  public $rightEyeClosedProbability;
  /**
   * @var float
   */
  public $rollAngle;
  /**
   * @var string
   */
  public $signature;
  /**
   * @var float
   */
  public $skinBrightnessProbability;
  /**
   * @var float
   */
  public $sorrowProbability;
  /**
   * @var float
   */
  public $surpriseProbability;
  /**
   * @var float
   */
  public $tiltAngle;
  /**
   * @var float
   */
  public $underExposedProbability;
  protected $versionedSignaturesType = PhotosVisionServiceFaceVersionedFaceSignature::class;
  protected $versionedSignaturesDataType = 'array';

  /**
   * @param float
   */
  public function setAge($age)
  {
    $this->age = $age;
  }
  /**
   * @return float
   */
  public function getAge()
  {
    return $this->age;
  }
  /**
   * @param float
   */
  public function setAngerProbability($angerProbability)
  {
    $this->angerProbability = $angerProbability;
  }
  /**
   * @return float
   */
  public function getAngerProbability()
  {
    return $this->angerProbability;
  }
  /**
   * @param HumanSensingFaceAttribute[]
   */
  public function setAttribute($attribute)
  {
    $this->attribute = $attribute;
  }
  /**
   * @return HumanSensingFaceAttribute[]
   */
  public function getAttribute()
  {
    return $this->attribute;
  }
  /**
   * @param float
   */
  public function setBeardProbability($beardProbability)
  {
    $this->beardProbability = $beardProbability;
  }
  /**
   * @return float
   */
  public function getBeardProbability()
  {
    return $this->beardProbability;
  }
  /**
   * @param float
   */
  public function setBlurredProbability($blurredProbability)
  {
    $this->blurredProbability = $blurredProbability;
  }
  /**
   * @return float
   */
  public function getBlurredProbability()
  {
    return $this->blurredProbability;
  }
  /**
   * @param PhotosVisionServiceFaceFaceParamsBoundingBox
   */
  public function setBoundingBox(PhotosVisionServiceFaceFaceParamsBoundingBox $boundingBox)
  {
    $this->boundingBox = $boundingBox;
  }
  /**
   * @return PhotosVisionServiceFaceFaceParamsBoundingBox
   */
  public function getBoundingBox()
  {
    return $this->boundingBox;
  }
  /**
   * @param float
   */
  public function setDarkGlassesProbability($darkGlassesProbability)
  {
    $this->darkGlassesProbability = $darkGlassesProbability;
  }
  /**
   * @return float
   */
  public function getDarkGlassesProbability()
  {
    return $this->darkGlassesProbability;
  }
  /**
   * @param float
   */
  public function setDetectionConfidence($detectionConfidence)
  {
    $this->detectionConfidence = $detectionConfidence;
  }
  /**
   * @return float
   */
  public function getDetectionConfidence()
  {
    return $this->detectionConfidence;
  }
  /**
   * @param PhotosVisionServiceFaceFaceParamsExtendedLandmark[]
   */
  public function setExtendedLandmarks($extendedLandmarks)
  {
    $this->extendedLandmarks = $extendedLandmarks;
  }
  /**
   * @return PhotosVisionServiceFaceFaceParamsExtendedLandmark[]
   */
  public function getExtendedLandmarks()
  {
    return $this->extendedLandmarks;
  }
  /**
   * @param float
   */
  public function setEyesClosedProbability($eyesClosedProbability)
  {
    $this->eyesClosedProbability = $eyesClosedProbability;
  }
  /**
   * @return float
   */
  public function getEyesClosedProbability()
  {
    return $this->eyesClosedProbability;
  }
  /**
   * @param ResearchVisionFace2cartoonFace2CartoonResults
   */
  public function setFace2cartoonResults(ResearchVisionFace2cartoonFace2CartoonResults $face2cartoonResults)
  {
    $this->face2cartoonResults = $face2cartoonResults;
  }
  /**
   * @return ResearchVisionFace2cartoonFace2CartoonResults
   */
  public function getFace2cartoonResults()
  {
    return $this->face2cartoonResults;
  }
  /**
   * @param PhotosVisionServiceFaceFaceParamsFaceCropV8
   */
  public function setFaceCropV8(PhotosVisionServiceFaceFaceParamsFaceCropV8 $faceCropV8)
  {
    $this->faceCropV8 = $faceCropV8;
  }
  /**
   * @return PhotosVisionServiceFaceFaceParamsFaceCropV8
   */
  public function getFaceCropV8()
  {
    return $this->faceCropV8;
  }
  /**
   * @param PhotosVisionServiceFaceFaceParamsBoundingBox
   */
  public function setFdBoundingBox(PhotosVisionServiceFaceFaceParamsBoundingBox $fdBoundingBox)
  {
    $this->fdBoundingBox = $fdBoundingBox;
  }
  /**
   * @return PhotosVisionServiceFaceFaceParamsBoundingBox
   */
  public function getFdBoundingBox()
  {
    return $this->fdBoundingBox;
  }
  /**
   * @param float
   */
  public function setFemaleProbability($femaleProbability)
  {
    $this->femaleProbability = $femaleProbability;
  }
  /**
   * @return float
   */
  public function getFemaleProbability()
  {
    return $this->femaleProbability;
  }
  /**
   * @param float
   */
  public function setFrontalGazeProbability($frontalGazeProbability)
  {
    $this->frontalGazeProbability = $frontalGazeProbability;
  }
  /**
   * @return float
   */
  public function getFrontalGazeProbability()
  {
    return $this->frontalGazeProbability;
  }
  /**
   * @param float
   */
  public function setGlassesProbability($glassesProbability)
  {
    $this->glassesProbability = $glassesProbability;
  }
  /**
   * @return float
   */
  public function getGlassesProbability()
  {
    return $this->glassesProbability;
  }
  /**
   * @param float
   */
  public function setHeadwearProbability($headwearProbability)
  {
    $this->headwearProbability = $headwearProbability;
  }
  /**
   * @return float
   */
  public function getHeadwearProbability()
  {
    return $this->headwearProbability;
  }
  /**
   * @param PhotosVisionServiceFaceImageParams
   */
  public function setImageParams(PhotosVisionServiceFaceImageParams $imageParams)
  {
    $this->imageParams = $imageParams;
  }
  /**
   * @return PhotosVisionServiceFaceImageParams
   */
  public function getImageParams()
  {
    return $this->imageParams;
  }
  /**
   * @param float
   */
  public function setJoyProbability($joyProbability)
  {
    $this->joyProbability = $joyProbability;
  }
  /**
   * @return float
   */
  public function getJoyProbability()
  {
    return $this->joyProbability;
  }
  /**
   * @param PhotosVisionServiceFaceFaceParamsLandmarkPosition[]
   */
  public function setLandmarkPositions($landmarkPositions)
  {
    $this->landmarkPositions = $landmarkPositions;
  }
  /**
   * @return PhotosVisionServiceFaceFaceParamsLandmarkPosition[]
   */
  public function getLandmarkPositions()
  {
    return $this->landmarkPositions;
  }
  /**
   * @param float
   */
  public function setLandmarkingConfidence($landmarkingConfidence)
  {
    $this->landmarkingConfidence = $landmarkingConfidence;
  }
  /**
   * @return float
   */
  public function getLandmarkingConfidence()
  {
    return $this->landmarkingConfidence;
  }
  /**
   * @param float
   */
  public function setLeftEyeClosedProbability($leftEyeClosedProbability)
  {
    $this->leftEyeClosedProbability = $leftEyeClosedProbability;
  }
  /**
   * @return float
   */
  public function getLeftEyeClosedProbability()
  {
    return $this->leftEyeClosedProbability;
  }
  /**
   * @param float
   */
  public function setLongHairProbability($longHairProbability)
  {
    $this->longHairProbability = $longHairProbability;
  }
  /**
   * @return float
   */
  public function getLongHairProbability()
  {
    return $this->longHairProbability;
  }
  /**
   * @param float
   */
  public function setMouthOpenProbability($mouthOpenProbability)
  {
    $this->mouthOpenProbability = $mouthOpenProbability;
  }
  /**
   * @return float
   */
  public function getMouthOpenProbability()
  {
    return $this->mouthOpenProbability;
  }
  /**
   * @param float
   */
  public function setNonHumanProbability($nonHumanProbability)
  {
    $this->nonHumanProbability = $nonHumanProbability;
  }
  /**
   * @return float
   */
  public function getNonHumanProbability()
  {
    return $this->nonHumanProbability;
  }
  /**
   * @param float
   */
  public function setPanAngle($panAngle)
  {
    $this->panAngle = $panAngle;
  }
  /**
   * @return float
   */
  public function getPanAngle()
  {
    return $this->panAngle;
  }
  /**
   * @param PhotosVisionServiceFaceFaceParamsPoseMatrix
   */
  public function setPoseMatrix(PhotosVisionServiceFaceFaceParamsPoseMatrix $poseMatrix)
  {
    $this->poseMatrix = $poseMatrix;
  }
  /**
   * @return PhotosVisionServiceFaceFaceParamsPoseMatrix
   */
  public function getPoseMatrix()
  {
    return $this->poseMatrix;
  }
  /**
   * @param string
   */
  public function setPretemplate($pretemplate)
  {
    $this->pretemplate = $pretemplate;
  }
  /**
   * @return string
   */
  public function getPretemplate()
  {
    return $this->pretemplate;
  }
  /**
   * @param float
   */
  public function setQualityScore($qualityScore)
  {
    $this->qualityScore = $qualityScore;
  }
  /**
   * @return float
   */
  public function getQualityScore()
  {
    return $this->qualityScore;
  }
  /**
   * @param float
   */
  public function setRightEyeClosedProbability($rightEyeClosedProbability)
  {
    $this->rightEyeClosedProbability = $rightEyeClosedProbability;
  }
  /**
   * @return float
   */
  public function getRightEyeClosedProbability()
  {
    return $this->rightEyeClosedProbability;
  }
  /**
   * @param float
   */
  public function setRollAngle($rollAngle)
  {
    $this->rollAngle = $rollAngle;
  }
  /**
   * @return float
   */
  public function getRollAngle()
  {
    return $this->rollAngle;
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
   * @param float
   */
  public function setSkinBrightnessProbability($skinBrightnessProbability)
  {
    $this->skinBrightnessProbability = $skinBrightnessProbability;
  }
  /**
   * @return float
   */
  public function getSkinBrightnessProbability()
  {
    return $this->skinBrightnessProbability;
  }
  /**
   * @param float
   */
  public function setSorrowProbability($sorrowProbability)
  {
    $this->sorrowProbability = $sorrowProbability;
  }
  /**
   * @return float
   */
  public function getSorrowProbability()
  {
    return $this->sorrowProbability;
  }
  /**
   * @param float
   */
  public function setSurpriseProbability($surpriseProbability)
  {
    $this->surpriseProbability = $surpriseProbability;
  }
  /**
   * @return float
   */
  public function getSurpriseProbability()
  {
    return $this->surpriseProbability;
  }
  /**
   * @param float
   */
  public function setTiltAngle($tiltAngle)
  {
    $this->tiltAngle = $tiltAngle;
  }
  /**
   * @return float
   */
  public function getTiltAngle()
  {
    return $this->tiltAngle;
  }
  /**
   * @param float
   */
  public function setUnderExposedProbability($underExposedProbability)
  {
    $this->underExposedProbability = $underExposedProbability;
  }
  /**
   * @return float
   */
  public function getUnderExposedProbability()
  {
    return $this->underExposedProbability;
  }
  /**
   * @param PhotosVisionServiceFaceVersionedFaceSignature[]
   */
  public function setVersionedSignatures($versionedSignatures)
  {
    $this->versionedSignatures = $versionedSignatures;
  }
  /**
   * @return PhotosVisionServiceFaceVersionedFaceSignature[]
   */
  public function getVersionedSignatures()
  {
    return $this->versionedSignatures;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(PhotosVisionServiceFaceFaceParams::class, 'Google_Service_Contentwarehouse_PhotosVisionServiceFaceFaceParams');
