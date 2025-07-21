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

namespace Google\Service\APIhub;

class GoogleCloudApihubV1Spec extends \Google\Collection
{
  protected $collection_key = 'sourceMetadata';
  protected $attributesType = GoogleCloudApihubV1AttributeValues::class;
  protected $attributesDataType = 'map';
  protected $contentsType = GoogleCloudApihubV1SpecContents::class;
  protected $contentsDataType = '';
  /**
   * @var string
   */
  public $createTime;
  protected $detailsType = GoogleCloudApihubV1SpecDetails::class;
  protected $detailsDataType = '';
  /**
   * @var string
   */
  public $displayName;
  protected $documentationType = GoogleCloudApihubV1Documentation::class;
  protected $documentationDataType = '';
  protected $lintResponseType = GoogleCloudApihubV1LintResponse::class;
  protected $lintResponseDataType = '';
  /**
   * @var string
   */
  public $name;
  /**
   * @var string
   */
  public $parsingMode;
  protected $sourceMetadataType = GoogleCloudApihubV1SourceMetadata::class;
  protected $sourceMetadataDataType = 'array';
  /**
   * @var string
   */
  public $sourceUri;
  protected $specTypeType = GoogleCloudApihubV1AttributeValues::class;
  protected $specTypeDataType = '';
  /**
   * @var string
   */
  public $updateTime;

  /**
   * @param GoogleCloudApihubV1AttributeValues[]
   */
  public function setAttributes($attributes)
  {
    $this->attributes = $attributes;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues[]
   */
  public function getAttributes()
  {
    return $this->attributes;
  }
  /**
   * @param GoogleCloudApihubV1SpecContents
   */
  public function setContents(GoogleCloudApihubV1SpecContents $contents)
  {
    $this->contents = $contents;
  }
  /**
   * @return GoogleCloudApihubV1SpecContents
   */
  public function getContents()
  {
    return $this->contents;
  }
  /**
   * @param string
   */
  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  /**
   * @return string
   */
  public function getCreateTime()
  {
    return $this->createTime;
  }
  /**
   * @param GoogleCloudApihubV1SpecDetails
   */
  public function setDetails(GoogleCloudApihubV1SpecDetails $details)
  {
    $this->details = $details;
  }
  /**
   * @return GoogleCloudApihubV1SpecDetails
   */
  public function getDetails()
  {
    return $this->details;
  }
  /**
   * @param string
   */
  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  /**
   * @return string
   */
  public function getDisplayName()
  {
    return $this->displayName;
  }
  /**
   * @param GoogleCloudApihubV1Documentation
   */
  public function setDocumentation(GoogleCloudApihubV1Documentation $documentation)
  {
    $this->documentation = $documentation;
  }
  /**
   * @return GoogleCloudApihubV1Documentation
   */
  public function getDocumentation()
  {
    return $this->documentation;
  }
  /**
   * @param GoogleCloudApihubV1LintResponse
   */
  public function setLintResponse(GoogleCloudApihubV1LintResponse $lintResponse)
  {
    $this->lintResponse = $lintResponse;
  }
  /**
   * @return GoogleCloudApihubV1LintResponse
   */
  public function getLintResponse()
  {
    return $this->lintResponse;
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
  public function setParsingMode($parsingMode)
  {
    $this->parsingMode = $parsingMode;
  }
  /**
   * @return string
   */
  public function getParsingMode()
  {
    return $this->parsingMode;
  }
  /**
   * @param GoogleCloudApihubV1SourceMetadata[]
   */
  public function setSourceMetadata($sourceMetadata)
  {
    $this->sourceMetadata = $sourceMetadata;
  }
  /**
   * @return GoogleCloudApihubV1SourceMetadata[]
   */
  public function getSourceMetadata()
  {
    return $this->sourceMetadata;
  }
  /**
   * @param string
   */
  public function setSourceUri($sourceUri)
  {
    $this->sourceUri = $sourceUri;
  }
  /**
   * @return string
   */
  public function getSourceUri()
  {
    return $this->sourceUri;
  }
  /**
   * @param GoogleCloudApihubV1AttributeValues
   */
  public function setSpecType(GoogleCloudApihubV1AttributeValues $specType)
  {
    $this->specType = $specType;
  }
  /**
   * @return GoogleCloudApihubV1AttributeValues
   */
  public function getSpecType()
  {
    return $this->specType;
  }
  /**
   * @param string
   */
  public function setUpdateTime($updateTime)
  {
    $this->updateTime = $updateTime;
  }
  /**
   * @return string
   */
  public function getUpdateTime()
  {
    return $this->updateTime;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudApihubV1Spec::class, 'Google_Service_APIhub_GoogleCloudApihubV1Spec');
