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

class AssistantGroundingRankerContactGroundingProviderFeatures extends \Google\Model
{
  /**
   * @var string
   */
  public $conceptId;
  /**
   * @var string
   */
  public $contactSource;
  /**
   * @var bool
   */
  public $isRelationshipFromAnnotation;
  /**
   * @var bool
   */
  public $isRelationshipFromSource;
  /**
   * @var bool
   */
  public $isSingleCandidate;
  /**
   * @var bool
   */
  public $isStarred;
  /**
   * @var string
   */
  public $matchedNameType;
  /**
   * @var float
   */
  public $numAlternateNameFromFuzzyContactMatch;
  /**
   * @var float
   */
  public $numAlternateNamesFromS3;
  /**
   * @var float
   */
  public $numAlternativeNamesFromInterpretation;
  /**
   * @var float
   */
  public $numCandidates;
  /**
   * @var string
   */
  public $recognitionAlternateSource;

  /**
   * @param string
   */
  public function setConceptId($conceptId)
  {
    $this->conceptId = $conceptId;
  }
  /**
   * @return string
   */
  public function getConceptId()
  {
    return $this->conceptId;
  }
  /**
   * @param string
   */
  public function setContactSource($contactSource)
  {
    $this->contactSource = $contactSource;
  }
  /**
   * @return string
   */
  public function getContactSource()
  {
    return $this->contactSource;
  }
  /**
   * @param bool
   */
  public function setIsRelationshipFromAnnotation($isRelationshipFromAnnotation)
  {
    $this->isRelationshipFromAnnotation = $isRelationshipFromAnnotation;
  }
  /**
   * @return bool
   */
  public function getIsRelationshipFromAnnotation()
  {
    return $this->isRelationshipFromAnnotation;
  }
  /**
   * @param bool
   */
  public function setIsRelationshipFromSource($isRelationshipFromSource)
  {
    $this->isRelationshipFromSource = $isRelationshipFromSource;
  }
  /**
   * @return bool
   */
  public function getIsRelationshipFromSource()
  {
    return $this->isRelationshipFromSource;
  }
  /**
   * @param bool
   */
  public function setIsSingleCandidate($isSingleCandidate)
  {
    $this->isSingleCandidate = $isSingleCandidate;
  }
  /**
   * @return bool
   */
  public function getIsSingleCandidate()
  {
    return $this->isSingleCandidate;
  }
  /**
   * @param bool
   */
  public function setIsStarred($isStarred)
  {
    $this->isStarred = $isStarred;
  }
  /**
   * @return bool
   */
  public function getIsStarred()
  {
    return $this->isStarred;
  }
  /**
   * @param string
   */
  public function setMatchedNameType($matchedNameType)
  {
    $this->matchedNameType = $matchedNameType;
  }
  /**
   * @return string
   */
  public function getMatchedNameType()
  {
    return $this->matchedNameType;
  }
  /**
   * @param float
   */
  public function setNumAlternateNameFromFuzzyContactMatch($numAlternateNameFromFuzzyContactMatch)
  {
    $this->numAlternateNameFromFuzzyContactMatch = $numAlternateNameFromFuzzyContactMatch;
  }
  /**
   * @return float
   */
  public function getNumAlternateNameFromFuzzyContactMatch()
  {
    return $this->numAlternateNameFromFuzzyContactMatch;
  }
  /**
   * @param float
   */
  public function setNumAlternateNamesFromS3($numAlternateNamesFromS3)
  {
    $this->numAlternateNamesFromS3 = $numAlternateNamesFromS3;
  }
  /**
   * @return float
   */
  public function getNumAlternateNamesFromS3()
  {
    return $this->numAlternateNamesFromS3;
  }
  /**
   * @param float
   */
  public function setNumAlternativeNamesFromInterpretation($numAlternativeNamesFromInterpretation)
  {
    $this->numAlternativeNamesFromInterpretation = $numAlternativeNamesFromInterpretation;
  }
  /**
   * @return float
   */
  public function getNumAlternativeNamesFromInterpretation()
  {
    return $this->numAlternativeNamesFromInterpretation;
  }
  /**
   * @param float
   */
  public function setNumCandidates($numCandidates)
  {
    $this->numCandidates = $numCandidates;
  }
  /**
   * @return float
   */
  public function getNumCandidates()
  {
    return $this->numCandidates;
  }
  /**
   * @param string
   */
  public function setRecognitionAlternateSource($recognitionAlternateSource)
  {
    $this->recognitionAlternateSource = $recognitionAlternateSource;
  }
  /**
   * @return string
   */
  public function getRecognitionAlternateSource()
  {
    return $this->recognitionAlternateSource;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantGroundingRankerContactGroundingProviderFeatures::class, 'Google_Service_Contentwarehouse_AssistantGroundingRankerContactGroundingProviderFeatures');
