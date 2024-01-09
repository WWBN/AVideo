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

class AssistantGroundingRankerMediaGroundingProviderFeatures extends \Google\Model
{
  /**
   * @var string
   */
  public $albumReleaseType;
  /**
   * @var string
   */
  public $ambiguityClassifier;
  /**
   * @var bool
   */
  public $hasTypeSemanticEdge;
  /**
   * @var bool
   */
  public $isCastVideo;
  /**
   * @var bool
   */
  public $isMediaSearchQuerySubsetOfEntityNameAndArtist;
  /**
   * @var bool
   */
  public $isSeedRadio;
  /**
   * @var bool
   */
  public $isSeedRadioRequest;
  /**
   * @var string
   */
  public $mediaContentType;
  /**
   * @var float
   */
  public $mscRate;
  public $scubedPSaiMusic;
  public $scubedPSaiTvm;
  /**
   * @var string
   */
  public $type;
  public $youtubeConfidenceScore;

  /**
   * @param string
   */
  public function setAlbumReleaseType($albumReleaseType)
  {
    $this->albumReleaseType = $albumReleaseType;
  }
  /**
   * @return string
   */
  public function getAlbumReleaseType()
  {
    return $this->albumReleaseType;
  }
  /**
   * @param string
   */
  public function setAmbiguityClassifier($ambiguityClassifier)
  {
    $this->ambiguityClassifier = $ambiguityClassifier;
  }
  /**
   * @return string
   */
  public function getAmbiguityClassifier()
  {
    return $this->ambiguityClassifier;
  }
  /**
   * @param bool
   */
  public function setHasTypeSemanticEdge($hasTypeSemanticEdge)
  {
    $this->hasTypeSemanticEdge = $hasTypeSemanticEdge;
  }
  /**
   * @return bool
   */
  public function getHasTypeSemanticEdge()
  {
    return $this->hasTypeSemanticEdge;
  }
  /**
   * @param bool
   */
  public function setIsCastVideo($isCastVideo)
  {
    $this->isCastVideo = $isCastVideo;
  }
  /**
   * @return bool
   */
  public function getIsCastVideo()
  {
    return $this->isCastVideo;
  }
  /**
   * @param bool
   */
  public function setIsMediaSearchQuerySubsetOfEntityNameAndArtist($isMediaSearchQuerySubsetOfEntityNameAndArtist)
  {
    $this->isMediaSearchQuerySubsetOfEntityNameAndArtist = $isMediaSearchQuerySubsetOfEntityNameAndArtist;
  }
  /**
   * @return bool
   */
  public function getIsMediaSearchQuerySubsetOfEntityNameAndArtist()
  {
    return $this->isMediaSearchQuerySubsetOfEntityNameAndArtist;
  }
  /**
   * @param bool
   */
  public function setIsSeedRadio($isSeedRadio)
  {
    $this->isSeedRadio = $isSeedRadio;
  }
  /**
   * @return bool
   */
  public function getIsSeedRadio()
  {
    return $this->isSeedRadio;
  }
  /**
   * @param bool
   */
  public function setIsSeedRadioRequest($isSeedRadioRequest)
  {
    $this->isSeedRadioRequest = $isSeedRadioRequest;
  }
  /**
   * @return bool
   */
  public function getIsSeedRadioRequest()
  {
    return $this->isSeedRadioRequest;
  }
  /**
   * @param string
   */
  public function setMediaContentType($mediaContentType)
  {
    $this->mediaContentType = $mediaContentType;
  }
  /**
   * @return string
   */
  public function getMediaContentType()
  {
    return $this->mediaContentType;
  }
  /**
   * @param float
   */
  public function setMscRate($mscRate)
  {
    $this->mscRate = $mscRate;
  }
  /**
   * @return float
   */
  public function getMscRate()
  {
    return $this->mscRate;
  }
  public function setScubedPSaiMusic($scubedPSaiMusic)
  {
    $this->scubedPSaiMusic = $scubedPSaiMusic;
  }
  public function getScubedPSaiMusic()
  {
    return $this->scubedPSaiMusic;
  }
  public function setScubedPSaiTvm($scubedPSaiTvm)
  {
    $this->scubedPSaiTvm = $scubedPSaiTvm;
  }
  public function getScubedPSaiTvm()
  {
    return $this->scubedPSaiTvm;
  }
  /**
   * @param string
   */
  public function setType($type)
  {
    $this->type = $type;
  }
  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }
  public function setYoutubeConfidenceScore($youtubeConfidenceScore)
  {
    $this->youtubeConfidenceScore = $youtubeConfidenceScore;
  }
  public function getYoutubeConfidenceScore()
  {
    return $this->youtubeConfidenceScore;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantGroundingRankerMediaGroundingProviderFeatures::class, 'Google_Service_Contentwarehouse_AssistantGroundingRankerMediaGroundingProviderFeatures');
