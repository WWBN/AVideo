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

namespace Google\Service\DLP;

class GooglePrivacyDlpV2DataProfileFinding extends \Google\Model
{
  /**
   * @var string
   */
  public $dataProfileResourceName;
  protected $dataSourceTypeType = GooglePrivacyDlpV2DataSourceType::class;
  protected $dataSourceTypeDataType = '';
  /**
   * @var string
   */
  public $findingId;
  /**
   * @var string
   */
  public $fullResourceName;
  protected $infotypeType = GooglePrivacyDlpV2InfoType::class;
  protected $infotypeDataType = '';
  protected $locationType = GooglePrivacyDlpV2DataProfileFindingLocation::class;
  protected $locationDataType = '';
  /**
   * @var string
   */
  public $quote;
  protected $quoteInfoType = GooglePrivacyDlpV2QuoteInfo::class;
  protected $quoteInfoDataType = '';
  /**
   * @var string
   */
  public $resourceVisibility;
  /**
   * @var string
   */
  public $timestamp;

  /**
   * @param string
   */
  public function setDataProfileResourceName($dataProfileResourceName)
  {
    $this->dataProfileResourceName = $dataProfileResourceName;
  }
  /**
   * @return string
   */
  public function getDataProfileResourceName()
  {
    return $this->dataProfileResourceName;
  }
  /**
   * @param GooglePrivacyDlpV2DataSourceType
   */
  public function setDataSourceType(GooglePrivacyDlpV2DataSourceType $dataSourceType)
  {
    $this->dataSourceType = $dataSourceType;
  }
  /**
   * @return GooglePrivacyDlpV2DataSourceType
   */
  public function getDataSourceType()
  {
    return $this->dataSourceType;
  }
  /**
   * @param string
   */
  public function setFindingId($findingId)
  {
    $this->findingId = $findingId;
  }
  /**
   * @return string
   */
  public function getFindingId()
  {
    return $this->findingId;
  }
  /**
   * @param string
   */
  public function setFullResourceName($fullResourceName)
  {
    $this->fullResourceName = $fullResourceName;
  }
  /**
   * @return string
   */
  public function getFullResourceName()
  {
    return $this->fullResourceName;
  }
  /**
   * @param GooglePrivacyDlpV2InfoType
   */
  public function setInfotype(GooglePrivacyDlpV2InfoType $infotype)
  {
    $this->infotype = $infotype;
  }
  /**
   * @return GooglePrivacyDlpV2InfoType
   */
  public function getInfotype()
  {
    return $this->infotype;
  }
  /**
   * @param GooglePrivacyDlpV2DataProfileFindingLocation
   */
  public function setLocation(GooglePrivacyDlpV2DataProfileFindingLocation $location)
  {
    $this->location = $location;
  }
  /**
   * @return GooglePrivacyDlpV2DataProfileFindingLocation
   */
  public function getLocation()
  {
    return $this->location;
  }
  /**
   * @param string
   */
  public function setQuote($quote)
  {
    $this->quote = $quote;
  }
  /**
   * @return string
   */
  public function getQuote()
  {
    return $this->quote;
  }
  /**
   * @param GooglePrivacyDlpV2QuoteInfo
   */
  public function setQuoteInfo(GooglePrivacyDlpV2QuoteInfo $quoteInfo)
  {
    $this->quoteInfo = $quoteInfo;
  }
  /**
   * @return GooglePrivacyDlpV2QuoteInfo
   */
  public function getQuoteInfo()
  {
    return $this->quoteInfo;
  }
  /**
   * @param string
   */
  public function setResourceVisibility($resourceVisibility)
  {
    $this->resourceVisibility = $resourceVisibility;
  }
  /**
   * @return string
   */
  public function getResourceVisibility()
  {
    return $this->resourceVisibility;
  }
  /**
   * @param string
   */
  public function setTimestamp($timestamp)
  {
    $this->timestamp = $timestamp;
  }
  /**
   * @return string
   */
  public function getTimestamp()
  {
    return $this->timestamp;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GooglePrivacyDlpV2DataProfileFinding::class, 'Google_Service_DLP_GooglePrivacyDlpV2DataProfileFinding');
