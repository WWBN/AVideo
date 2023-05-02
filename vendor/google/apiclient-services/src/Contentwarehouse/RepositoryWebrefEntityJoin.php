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

class RepositoryWebrefEntityJoin extends \Google\Collection
{
  protected $collection_key = 'representation';
  protected $annotatedEntityIdType = RepositoryWebrefWebrefEntityId::class;
  protected $annotatedEntityIdDataType = '';
  public $annotatedEntityId;
  protected $cdocType = RepositoryWebrefSimplifiedCompositeDoc::class;
  protected $cdocDataType = 'array';
  public $cdoc;
  protected $contextNameInfoType = RepositoryWebrefGlobalNameInfo::class;
  protected $contextNameInfoDataType = 'array';
  public $contextNameInfo;
  protected $debugInfoType = RepositoryWebrefEntityDebugInfo::class;
  protected $debugInfoDataType = 'array';
  public $debugInfo;
  protected $enricherAnnotatorProfileType = RepositoryWebrefAnnotatorProfile::class;
  protected $enricherAnnotatorProfileDataType = '';
  public $enricherAnnotatorProfile;
  protected $enricherDebugDataType = RepositoryWebrefEnricherDebugData::class;
  protected $enricherDebugDataDataType = '';
  public $enricherDebugData;
  protected $extraDataType = RepositoryWebrefExtraMetadata::class;
  protected $extraDataDataType = '';
  public $extraData;
  protected $humanRatingsType = RepositoryWebrefHumanRatings::class;
  protected $humanRatingsDataType = '';
  public $humanRatings;
  protected $linkInfoType = RepositoryWebrefGlobalLinkInfo::class;
  protected $linkInfoDataType = 'array';
  public $linkInfo;
  protected $nameInfoType = RepositoryWebrefGlobalNameInfo::class;
  protected $nameInfoDataType = 'array';
  public $nameInfo;
  protected $refconNameInfoType = RepositoryWebrefRefconRefconNameInfo::class;
  protected $refconNameInfoDataType = 'array';
  public $refconNameInfo;
  protected $representationType = RepositoryWebrefDomainSpecificRepresentation::class;
  protected $representationDataType = 'array';
  public $representation;

  /**
   * @param RepositoryWebrefWebrefEntityId
   */
  public function setAnnotatedEntityId(RepositoryWebrefWebrefEntityId $annotatedEntityId)
  {
    $this->annotatedEntityId = $annotatedEntityId;
  }
  /**
   * @return RepositoryWebrefWebrefEntityId
   */
  public function getAnnotatedEntityId()
  {
    return $this->annotatedEntityId;
  }
  /**
   * @param RepositoryWebrefSimplifiedCompositeDoc[]
   */
  public function setCdoc($cdoc)
  {
    $this->cdoc = $cdoc;
  }
  /**
   * @return RepositoryWebrefSimplifiedCompositeDoc[]
   */
  public function getCdoc()
  {
    return $this->cdoc;
  }
  /**
   * @param RepositoryWebrefGlobalNameInfo[]
   */
  public function setContextNameInfo($contextNameInfo)
  {
    $this->contextNameInfo = $contextNameInfo;
  }
  /**
   * @return RepositoryWebrefGlobalNameInfo[]
   */
  public function getContextNameInfo()
  {
    return $this->contextNameInfo;
  }
  /**
   * @param RepositoryWebrefEntityDebugInfo[]
   */
  public function setDebugInfo($debugInfo)
  {
    $this->debugInfo = $debugInfo;
  }
  /**
   * @return RepositoryWebrefEntityDebugInfo[]
   */
  public function getDebugInfo()
  {
    return $this->debugInfo;
  }
  /**
   * @param RepositoryWebrefAnnotatorProfile
   */
  public function setEnricherAnnotatorProfile(RepositoryWebrefAnnotatorProfile $enricherAnnotatorProfile)
  {
    $this->enricherAnnotatorProfile = $enricherAnnotatorProfile;
  }
  /**
   * @return RepositoryWebrefAnnotatorProfile
   */
  public function getEnricherAnnotatorProfile()
  {
    return $this->enricherAnnotatorProfile;
  }
  /**
   * @param RepositoryWebrefEnricherDebugData
   */
  public function setEnricherDebugData(RepositoryWebrefEnricherDebugData $enricherDebugData)
  {
    $this->enricherDebugData = $enricherDebugData;
  }
  /**
   * @return RepositoryWebrefEnricherDebugData
   */
  public function getEnricherDebugData()
  {
    return $this->enricherDebugData;
  }
  /**
   * @param RepositoryWebrefExtraMetadata
   */
  public function setExtraData(RepositoryWebrefExtraMetadata $extraData)
  {
    $this->extraData = $extraData;
  }
  /**
   * @return RepositoryWebrefExtraMetadata
   */
  public function getExtraData()
  {
    return $this->extraData;
  }
  /**
   * @param RepositoryWebrefHumanRatings
   */
  public function setHumanRatings(RepositoryWebrefHumanRatings $humanRatings)
  {
    $this->humanRatings = $humanRatings;
  }
  /**
   * @return RepositoryWebrefHumanRatings
   */
  public function getHumanRatings()
  {
    return $this->humanRatings;
  }
  /**
   * @param RepositoryWebrefGlobalLinkInfo[]
   */
  public function setLinkInfo($linkInfo)
  {
    $this->linkInfo = $linkInfo;
  }
  /**
   * @return RepositoryWebrefGlobalLinkInfo[]
   */
  public function getLinkInfo()
  {
    return $this->linkInfo;
  }
  /**
   * @param RepositoryWebrefGlobalNameInfo[]
   */
  public function setNameInfo($nameInfo)
  {
    $this->nameInfo = $nameInfo;
  }
  /**
   * @return RepositoryWebrefGlobalNameInfo[]
   */
  public function getNameInfo()
  {
    return $this->nameInfo;
  }
  /**
   * @param RepositoryWebrefRefconRefconNameInfo[]
   */
  public function setRefconNameInfo($refconNameInfo)
  {
    $this->refconNameInfo = $refconNameInfo;
  }
  /**
   * @return RepositoryWebrefRefconRefconNameInfo[]
   */
  public function getRefconNameInfo()
  {
    return $this->refconNameInfo;
  }
  /**
   * @param RepositoryWebrefDomainSpecificRepresentation[]
   */
  public function setRepresentation($representation)
  {
    $this->representation = $representation;
  }
  /**
   * @return RepositoryWebrefDomainSpecificRepresentation[]
   */
  public function getRepresentation()
  {
    return $this->representation;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(RepositoryWebrefEntityJoin::class, 'Google_Service_Contentwarehouse_RepositoryWebrefEntityJoin');
