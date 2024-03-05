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

class ImageRepositoryLanguageIdentificationResult extends \Google\Model
{
  /**
   * @var bool
   */
  public $localeStripped;
  /**
   * @var string
   */
  public $s3TopLocale;
  /**
   * @var string
   */
  public $ytCapsAudioLanguage;

  /**
   * @param bool
   */
  public function setLocaleStripped($localeStripped)
  {
    $this->localeStripped = $localeStripped;
  }
  /**
   * @return bool
   */
  public function getLocaleStripped()
  {
    return $this->localeStripped;
  }
  /**
   * @param string
   */
  public function setS3TopLocale($s3TopLocale)
  {
    $this->s3TopLocale = $s3TopLocale;
  }
  /**
   * @return string
   */
  public function getS3TopLocale()
  {
    return $this->s3TopLocale;
  }
  /**
   * @param string
   */
  public function setYtCapsAudioLanguage($ytCapsAudioLanguage)
  {
    $this->ytCapsAudioLanguage = $ytCapsAudioLanguage;
  }
  /**
   * @return string
   */
  public function getYtCapsAudioLanguage()
  {
    return $this->ytCapsAudioLanguage;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ImageRepositoryLanguageIdentificationResult::class, 'Google_Service_Contentwarehouse_ImageRepositoryLanguageIdentificationResult');
