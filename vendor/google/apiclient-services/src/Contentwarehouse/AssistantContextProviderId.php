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

class AssistantContextProviderId extends \Google\Model
{
  protected $appProviderIdType = AssistantContextAppProviderId::class;
  protected $appProviderIdDataType = '';
  /**
   * @var string
   */
  public $ecosystemType;
  protected $mediaProviderIdType = AssistantContextMediaProviderId::class;
  protected $mediaProviderIdDataType = '';
  /**
   * @var string
   */
  public $mid;
  /**
   * @var string
   */
  public $providerCorpusId;
  protected $providerVariantType = AssistantContextProviderVariant::class;
  protected $providerVariantDataType = '';

  /**
   * @param AssistantContextAppProviderId
   */
  public function setAppProviderId(AssistantContextAppProviderId $appProviderId)
  {
    $this->appProviderId = $appProviderId;
  }
  /**
   * @return AssistantContextAppProviderId
   */
  public function getAppProviderId()
  {
    return $this->appProviderId;
  }
  /**
   * @param string
   */
  public function setEcosystemType($ecosystemType)
  {
    $this->ecosystemType = $ecosystemType;
  }
  /**
   * @return string
   */
  public function getEcosystemType()
  {
    return $this->ecosystemType;
  }
  /**
   * @param AssistantContextMediaProviderId
   */
  public function setMediaProviderId(AssistantContextMediaProviderId $mediaProviderId)
  {
    $this->mediaProviderId = $mediaProviderId;
  }
  /**
   * @return AssistantContextMediaProviderId
   */
  public function getMediaProviderId()
  {
    return $this->mediaProviderId;
  }
  /**
   * @param string
   */
  public function setMid($mid)
  {
    $this->mid = $mid;
  }
  /**
   * @return string
   */
  public function getMid()
  {
    return $this->mid;
  }
  /**
   * @param string
   */
  public function setProviderCorpusId($providerCorpusId)
  {
    $this->providerCorpusId = $providerCorpusId;
  }
  /**
   * @return string
   */
  public function getProviderCorpusId()
  {
    return $this->providerCorpusId;
  }
  /**
   * @param AssistantContextProviderVariant
   */
  public function setProviderVariant(AssistantContextProviderVariant $providerVariant)
  {
    $this->providerVariant = $providerVariant;
  }
  /**
   * @return AssistantContextProviderVariant
   */
  public function getProviderVariant()
  {
    return $this->providerVariant;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantContextProviderId::class, 'Google_Service_Contentwarehouse_AssistantContextProviderId');
