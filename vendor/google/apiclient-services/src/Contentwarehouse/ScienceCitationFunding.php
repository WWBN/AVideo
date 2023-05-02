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

class ScienceCitationFunding extends \Google\Collection
{
  protected $collection_key = 'DebugExtractionInfo';
  protected $internal_gapi_mappings = [
        "agency" => "Agency",
        "agencyName" => "AgencyName",
        "debugExtractionInfo" => "DebugExtractionInfo",
        "debugFundingTextBlock" => "DebugFundingTextBlock",
        "grantNumber" => "GrantNumber",
        "recipient" => "Recipient",
        "sourceText" => "SourceText",
        "urlBasedFundingSource" => "UrlBasedFundingSource",
  ];
  /**
   * @var int
   */
  public $agency;
  /**
   * @var string
   */
  public $agencyName;
  protected $debugExtractionInfoType = ScienceCitationFundingExtractionInfo::class;
  protected $debugExtractionInfoDataType = 'array';
  public $debugExtractionInfo;
  /**
   * @var string
   */
  public $debugFundingTextBlock;
  /**
   * @var string
   */
  public $grantNumber;
  /**
   * @var string
   */
  public $recipient;
  /**
   * @var string
   */
  public $sourceText;
  /**
   * @var bool
   */
  public $urlBasedFundingSource;

  /**
   * @param int
   */
  public function setAgency($agency)
  {
    $this->agency = $agency;
  }
  /**
   * @return int
   */
  public function getAgency()
  {
    return $this->agency;
  }
  /**
   * @param string
   */
  public function setAgencyName($agencyName)
  {
    $this->agencyName = $agencyName;
  }
  /**
   * @return string
   */
  public function getAgencyName()
  {
    return $this->agencyName;
  }
  /**
   * @param ScienceCitationFundingExtractionInfo[]
   */
  public function setDebugExtractionInfo($debugExtractionInfo)
  {
    $this->debugExtractionInfo = $debugExtractionInfo;
  }
  /**
   * @return ScienceCitationFundingExtractionInfo[]
   */
  public function getDebugExtractionInfo()
  {
    return $this->debugExtractionInfo;
  }
  /**
   * @param string
   */
  public function setDebugFundingTextBlock($debugFundingTextBlock)
  {
    $this->debugFundingTextBlock = $debugFundingTextBlock;
  }
  /**
   * @return string
   */
  public function getDebugFundingTextBlock()
  {
    return $this->debugFundingTextBlock;
  }
  /**
   * @param string
   */
  public function setGrantNumber($grantNumber)
  {
    $this->grantNumber = $grantNumber;
  }
  /**
   * @return string
   */
  public function getGrantNumber()
  {
    return $this->grantNumber;
  }
  /**
   * @param string
   */
  public function setRecipient($recipient)
  {
    $this->recipient = $recipient;
  }
  /**
   * @return string
   */
  public function getRecipient()
  {
    return $this->recipient;
  }
  /**
   * @param string
   */
  public function setSourceText($sourceText)
  {
    $this->sourceText = $sourceText;
  }
  /**
   * @return string
   */
  public function getSourceText()
  {
    return $this->sourceText;
  }
  /**
   * @param bool
   */
  public function setUrlBasedFundingSource($urlBasedFundingSource)
  {
    $this->urlBasedFundingSource = $urlBasedFundingSource;
  }
  /**
   * @return bool
   */
  public function getUrlBasedFundingSource()
  {
    return $this->urlBasedFundingSource;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ScienceCitationFunding::class, 'Google_Service_Contentwarehouse_ScienceCitationFunding');
