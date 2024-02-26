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

class AppsPeopleOzExternalMergedpeopleapiNamePronunciationAudioMetadataInfo extends \Google\Model
{
  protected $metadataType = AppsPeopleOzExternalMergedpeopleapiPersonFieldMetadata::class;
  protected $metadataDataType = '';
  protected $namePronunciationAudioMetadataType = SocialGraphApiProtoNamePronunciationAudioMetadata::class;
  protected $namePronunciationAudioMetadataDataType = '';

  /**
   * @param AppsPeopleOzExternalMergedpeopleapiPersonFieldMetadata
   */
  public function setMetadata(AppsPeopleOzExternalMergedpeopleapiPersonFieldMetadata $metadata)
  {
    $this->metadata = $metadata;
  }
  /**
   * @return AppsPeopleOzExternalMergedpeopleapiPersonFieldMetadata
   */
  public function getMetadata()
  {
    return $this->metadata;
  }
  /**
   * @param SocialGraphApiProtoNamePronunciationAudioMetadata
   */
  public function setNamePronunciationAudioMetadata(SocialGraphApiProtoNamePronunciationAudioMetadata $namePronunciationAudioMetadata)
  {
    $this->namePronunciationAudioMetadata = $namePronunciationAudioMetadata;
  }
  /**
   * @return SocialGraphApiProtoNamePronunciationAudioMetadata
   */
  public function getNamePronunciationAudioMetadata()
  {
    return $this->namePronunciationAudioMetadata;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AppsPeopleOzExternalMergedpeopleapiNamePronunciationAudioMetadataInfo::class, 'Google_Service_Contentwarehouse_AppsPeopleOzExternalMergedpeopleapiNamePronunciationAudioMetadataInfo');
