<?php
/*
 * Copyright 2016 Google Inc.
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

class Google_Service_Vision_AnnotateImageResponse extends Google_Collection
{
  protected $collection_key = 'textAnnotations';
  protected $cropHintsAnnotationType = 'Google_Service_Vision_CropHintsAnnotation';
  protected $cropHintsAnnotationDataType = '';
  protected $errorType = 'Google_Service_Vision_Status';
  protected $errorDataType = '';
  protected $faceAnnotationsType = 'Google_Service_Vision_FaceAnnotation';
  protected $faceAnnotationsDataType = 'array';
  protected $fullTextAnnotationType = 'Google_Service_Vision_TextAnnotation';
  protected $fullTextAnnotationDataType = '';
  protected $imagePropertiesAnnotationType = 'Google_Service_Vision_ImageProperties';
  protected $imagePropertiesAnnotationDataType = '';
  protected $labelAnnotationsType = 'Google_Service_Vision_EntityAnnotation';
  protected $labelAnnotationsDataType = 'array';
  protected $landmarkAnnotationsType = 'Google_Service_Vision_EntityAnnotation';
  protected $landmarkAnnotationsDataType = 'array';
  protected $logoAnnotationsType = 'Google_Service_Vision_EntityAnnotation';
  protected $logoAnnotationsDataType = 'array';
  protected $safeSearchAnnotationType = 'Google_Service_Vision_SafeSearchAnnotation';
  protected $safeSearchAnnotationDataType = '';
  protected $textAnnotationsType = 'Google_Service_Vision_EntityAnnotation';
  protected $textAnnotationsDataType = 'array';
  protected $webDetectionType = 'Google_Service_Vision_WebDetection';
  protected $webDetectionDataType = '';

  public function setCropHintsAnnotation(Google_Service_Vision_CropHintsAnnotation $cropHintsAnnotation)
  {
    $this->cropHintsAnnotation = $cropHintsAnnotation;
  }
  public function getCropHintsAnnotation()
  {
    return $this->cropHintsAnnotation;
  }
  public function setError(Google_Service_Vision_Status $error)
  {
    $this->error = $error;
  }
  public function getError()
  {
    return $this->error;
  }
  public function setFaceAnnotations($faceAnnotations)
  {
    $this->faceAnnotations = $faceAnnotations;
  }
  public function getFaceAnnotations()
  {
    return $this->faceAnnotations;
  }
  public function setFullTextAnnotation(Google_Service_Vision_TextAnnotation $fullTextAnnotation)
  {
    $this->fullTextAnnotation = $fullTextAnnotation;
  }
  public function getFullTextAnnotation()
  {
    return $this->fullTextAnnotation;
  }
  public function setImagePropertiesAnnotation(Google_Service_Vision_ImageProperties $imagePropertiesAnnotation)
  {
    $this->imagePropertiesAnnotation = $imagePropertiesAnnotation;
  }
  public function getImagePropertiesAnnotation()
  {
    return $this->imagePropertiesAnnotation;
  }
  public function setLabelAnnotations($labelAnnotations)
  {
    $this->labelAnnotations = $labelAnnotations;
  }
  public function getLabelAnnotations()
  {
    return $this->labelAnnotations;
  }
  public function setLandmarkAnnotations($landmarkAnnotations)
  {
    $this->landmarkAnnotations = $landmarkAnnotations;
  }
  public function getLandmarkAnnotations()
  {
    return $this->landmarkAnnotations;
  }
  public function setLogoAnnotations($logoAnnotations)
  {
    $this->logoAnnotations = $logoAnnotations;
  }
  public function getLogoAnnotations()
  {
    return $this->logoAnnotations;
  }
  public function setSafeSearchAnnotation(Google_Service_Vision_SafeSearchAnnotation $safeSearchAnnotation)
  {
    $this->safeSearchAnnotation = $safeSearchAnnotation;
  }
  public function getSafeSearchAnnotation()
  {
    return $this->safeSearchAnnotation;
  }
  public function setTextAnnotations($textAnnotations)
  {
    $this->textAnnotations = $textAnnotations;
  }
  public function getTextAnnotations()
  {
    return $this->textAnnotations;
  }
  public function setWebDetection(Google_Service_Vision_WebDetection $webDetection)
  {
    $this->webDetection = $webDetection;
  }
  public function getWebDetection()
  {
    return $this->webDetection;
  }
}
