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

class AssistantGroundingProviderProviderSignalResult extends \Google\Model
{
  /**
   * @var bool
   */
  public $isDefaultProvider;
  /**
   * @var bool
   */
  public $isForegroundProvider;
  /**
   * @var bool
   */
  public $isInAppProvider;
  /**
   * @var bool
   */
  public $isInstalled;
  /**
   * @var bool
   */
  public $isLastUsedProvider;
  /**
   * @var bool
   */
  public $isQueryRestrictedProvider;
  protected $providerSelectionResultType = AssistantContextProviderSelectionResult::class;
  protected $providerSelectionResultDataType = '';
  /**
   * @var string
   */
  public $providerTypeSignal;

  /**
   * @param bool
   */
  public function setIsDefaultProvider($isDefaultProvider)
  {
    $this->isDefaultProvider = $isDefaultProvider;
  }
  /**
   * @return bool
   */
  public function getIsDefaultProvider()
  {
    return $this->isDefaultProvider;
  }
  /**
   * @param bool
   */
  public function setIsForegroundProvider($isForegroundProvider)
  {
    $this->isForegroundProvider = $isForegroundProvider;
  }
  /**
   * @return bool
   */
  public function getIsForegroundProvider()
  {
    return $this->isForegroundProvider;
  }
  /**
   * @param bool
   */
  public function setIsInAppProvider($isInAppProvider)
  {
    $this->isInAppProvider = $isInAppProvider;
  }
  /**
   * @return bool
   */
  public function getIsInAppProvider()
  {
    return $this->isInAppProvider;
  }
  /**
   * @param bool
   */
  public function setIsInstalled($isInstalled)
  {
    $this->isInstalled = $isInstalled;
  }
  /**
   * @return bool
   */
  public function getIsInstalled()
  {
    return $this->isInstalled;
  }
  /**
   * @param bool
   */
  public function setIsLastUsedProvider($isLastUsedProvider)
  {
    $this->isLastUsedProvider = $isLastUsedProvider;
  }
  /**
   * @return bool
   */
  public function getIsLastUsedProvider()
  {
    return $this->isLastUsedProvider;
  }
  /**
   * @param bool
   */
  public function setIsQueryRestrictedProvider($isQueryRestrictedProvider)
  {
    $this->isQueryRestrictedProvider = $isQueryRestrictedProvider;
  }
  /**
   * @return bool
   */
  public function getIsQueryRestrictedProvider()
  {
    return $this->isQueryRestrictedProvider;
  }
  /**
   * @param AssistantContextProviderSelectionResult
   */
  public function setProviderSelectionResult(AssistantContextProviderSelectionResult $providerSelectionResult)
  {
    $this->providerSelectionResult = $providerSelectionResult;
  }
  /**
   * @return AssistantContextProviderSelectionResult
   */
  public function getProviderSelectionResult()
  {
    return $this->providerSelectionResult;
  }
  /**
   * @param string
   */
  public function setProviderTypeSignal($providerTypeSignal)
  {
    $this->providerTypeSignal = $providerTypeSignal;
  }
  /**
   * @return string
   */
  public function getProviderTypeSignal()
  {
    return $this->providerTypeSignal;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(AssistantGroundingProviderProviderSignalResult::class, 'Google_Service_Contentwarehouse_AssistantGroundingProviderProviderSignalResult');
