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

namespace Google\Service\SA360;

class GoogleAdsSearchads360V0ServicesSearchAds360Row extends \Google\Collection
{
  protected $collection_key = 'customColumns';
  protected $adGroupType = GoogleAdsSearchads360V0ResourcesAdGroup::class;
  protected $adGroupDataType = '';
  public $adGroup;
  protected $adGroupBidModifierType = GoogleAdsSearchads360V0ResourcesAdGroupBidModifier::class;
  protected $adGroupBidModifierDataType = '';
  public $adGroupBidModifier;
  protected $adGroupCriterionType = GoogleAdsSearchads360V0ResourcesAdGroupCriterion::class;
  protected $adGroupCriterionDataType = '';
  public $adGroupCriterion;
  protected $biddingStrategyType = GoogleAdsSearchads360V0ResourcesBiddingStrategy::class;
  protected $biddingStrategyDataType = '';
  public $biddingStrategy;
  protected $campaignType = GoogleAdsSearchads360V0ResourcesCampaign::class;
  protected $campaignDataType = '';
  public $campaign;
  protected $campaignBudgetType = GoogleAdsSearchads360V0ResourcesCampaignBudget::class;
  protected $campaignBudgetDataType = '';
  public $campaignBudget;
  protected $campaignCriterionType = GoogleAdsSearchads360V0ResourcesCampaignCriterion::class;
  protected $campaignCriterionDataType = '';
  public $campaignCriterion;
  protected $conversionActionType = GoogleAdsSearchads360V0ResourcesConversionAction::class;
  protected $conversionActionDataType = '';
  public $conversionAction;
  protected $customColumnsType = GoogleAdsSearchads360V0CommonValue::class;
  protected $customColumnsDataType = 'array';
  public $customColumns;
  protected $customerType = GoogleAdsSearchads360V0ResourcesCustomer::class;
  protected $customerDataType = '';
  public $customer;
  protected $customerClientType = GoogleAdsSearchads360V0ResourcesCustomerClient::class;
  protected $customerClientDataType = '';
  public $customerClient;
  protected $customerManagerLinkType = GoogleAdsSearchads360V0ResourcesCustomerManagerLink::class;
  protected $customerManagerLinkDataType = '';
  public $customerManagerLink;
  protected $keywordViewType = GoogleAdsSearchads360V0ResourcesKeywordView::class;
  protected $keywordViewDataType = '';
  public $keywordView;
  protected $metricsType = GoogleAdsSearchads360V0CommonMetrics::class;
  protected $metricsDataType = '';
  public $metrics;
  protected $productGroupViewType = GoogleAdsSearchads360V0ResourcesProductGroupView::class;
  protected $productGroupViewDataType = '';
  public $productGroupView;
  protected $segmentsType = GoogleAdsSearchads360V0CommonSegments::class;
  protected $segmentsDataType = '';
  public $segments;

  /**
   * @param GoogleAdsSearchads360V0ResourcesAdGroup
   */
  public function setAdGroup(GoogleAdsSearchads360V0ResourcesAdGroup $adGroup)
  {
    $this->adGroup = $adGroup;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesAdGroup
   */
  public function getAdGroup()
  {
    return $this->adGroup;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesAdGroupBidModifier
   */
  public function setAdGroupBidModifier(GoogleAdsSearchads360V0ResourcesAdGroupBidModifier $adGroupBidModifier)
  {
    $this->adGroupBidModifier = $adGroupBidModifier;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesAdGroupBidModifier
   */
  public function getAdGroupBidModifier()
  {
    return $this->adGroupBidModifier;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesAdGroupCriterion
   */
  public function setAdGroupCriterion(GoogleAdsSearchads360V0ResourcesAdGroupCriterion $adGroupCriterion)
  {
    $this->adGroupCriterion = $adGroupCriterion;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesAdGroupCriterion
   */
  public function getAdGroupCriterion()
  {
    return $this->adGroupCriterion;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesBiddingStrategy
   */
  public function setBiddingStrategy(GoogleAdsSearchads360V0ResourcesBiddingStrategy $biddingStrategy)
  {
    $this->biddingStrategy = $biddingStrategy;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesBiddingStrategy
   */
  public function getBiddingStrategy()
  {
    return $this->biddingStrategy;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesCampaign
   */
  public function setCampaign(GoogleAdsSearchads360V0ResourcesCampaign $campaign)
  {
    $this->campaign = $campaign;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesCampaign
   */
  public function getCampaign()
  {
    return $this->campaign;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesCampaignBudget
   */
  public function setCampaignBudget(GoogleAdsSearchads360V0ResourcesCampaignBudget $campaignBudget)
  {
    $this->campaignBudget = $campaignBudget;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesCampaignBudget
   */
  public function getCampaignBudget()
  {
    return $this->campaignBudget;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesCampaignCriterion
   */
  public function setCampaignCriterion(GoogleAdsSearchads360V0ResourcesCampaignCriterion $campaignCriterion)
  {
    $this->campaignCriterion = $campaignCriterion;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesCampaignCriterion
   */
  public function getCampaignCriterion()
  {
    return $this->campaignCriterion;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesConversionAction
   */
  public function setConversionAction(GoogleAdsSearchads360V0ResourcesConversionAction $conversionAction)
  {
    $this->conversionAction = $conversionAction;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesConversionAction
   */
  public function getConversionAction()
  {
    return $this->conversionAction;
  }
  /**
   * @param GoogleAdsSearchads360V0CommonValue[]
   */
  public function setCustomColumns($customColumns)
  {
    $this->customColumns = $customColumns;
  }
  /**
   * @return GoogleAdsSearchads360V0CommonValue[]
   */
  public function getCustomColumns()
  {
    return $this->customColumns;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesCustomer
   */
  public function setCustomer(GoogleAdsSearchads360V0ResourcesCustomer $customer)
  {
    $this->customer = $customer;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesCustomer
   */
  public function getCustomer()
  {
    return $this->customer;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesCustomerClient
   */
  public function setCustomerClient(GoogleAdsSearchads360V0ResourcesCustomerClient $customerClient)
  {
    $this->customerClient = $customerClient;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesCustomerClient
   */
  public function getCustomerClient()
  {
    return $this->customerClient;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesCustomerManagerLink
   */
  public function setCustomerManagerLink(GoogleAdsSearchads360V0ResourcesCustomerManagerLink $customerManagerLink)
  {
    $this->customerManagerLink = $customerManagerLink;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesCustomerManagerLink
   */
  public function getCustomerManagerLink()
  {
    return $this->customerManagerLink;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesKeywordView
   */
  public function setKeywordView(GoogleAdsSearchads360V0ResourcesKeywordView $keywordView)
  {
    $this->keywordView = $keywordView;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesKeywordView
   */
  public function getKeywordView()
  {
    return $this->keywordView;
  }
  /**
   * @param GoogleAdsSearchads360V0CommonMetrics
   */
  public function setMetrics(GoogleAdsSearchads360V0CommonMetrics $metrics)
  {
    $this->metrics = $metrics;
  }
  /**
   * @return GoogleAdsSearchads360V0CommonMetrics
   */
  public function getMetrics()
  {
    return $this->metrics;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesProductGroupView
   */
  public function setProductGroupView(GoogleAdsSearchads360V0ResourcesProductGroupView $productGroupView)
  {
    $this->productGroupView = $productGroupView;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesProductGroupView
   */
  public function getProductGroupView()
  {
    return $this->productGroupView;
  }
  /**
   * @param GoogleAdsSearchads360V0CommonSegments
   */
  public function setSegments(GoogleAdsSearchads360V0CommonSegments $segments)
  {
    $this->segments = $segments;
  }
  /**
   * @return GoogleAdsSearchads360V0CommonSegments
   */
  public function getSegments()
  {
    return $this->segments;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleAdsSearchads360V0ServicesSearchAds360Row::class, 'Google_Service_SA360_GoogleAdsSearchads360V0ServicesSearchAds360Row');
