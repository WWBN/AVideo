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
  protected $adGroupAdType = GoogleAdsSearchads360V0ResourcesAdGroupAd::class;
  protected $adGroupAdDataType = '';
  protected $adGroupAdLabelType = GoogleAdsSearchads360V0ResourcesAdGroupAdLabel::class;
  protected $adGroupAdLabelDataType = '';
  protected $adGroupAudienceViewType = GoogleAdsSearchads360V0ResourcesAdGroupAudienceView::class;
  protected $adGroupAudienceViewDataType = '';
  protected $adGroupBidModifierType = GoogleAdsSearchads360V0ResourcesAdGroupBidModifier::class;
  protected $adGroupBidModifierDataType = '';
  protected $adGroupCriterionType = GoogleAdsSearchads360V0ResourcesAdGroupCriterion::class;
  protected $adGroupCriterionDataType = '';
  protected $adGroupCriterionLabelType = GoogleAdsSearchads360V0ResourcesAdGroupCriterionLabel::class;
  protected $adGroupCriterionLabelDataType = '';
  protected $adGroupLabelType = GoogleAdsSearchads360V0ResourcesAdGroupLabel::class;
  protected $adGroupLabelDataType = '';
  protected $ageRangeViewType = GoogleAdsSearchads360V0ResourcesAgeRangeView::class;
  protected $ageRangeViewDataType = '';
  protected $biddingStrategyType = GoogleAdsSearchads360V0ResourcesBiddingStrategy::class;
  protected $biddingStrategyDataType = '';
  protected $campaignType = GoogleAdsSearchads360V0ResourcesCampaign::class;
  protected $campaignDataType = '';
  protected $campaignAudienceViewType = GoogleAdsSearchads360V0ResourcesCampaignAudienceView::class;
  protected $campaignAudienceViewDataType = '';
  protected $campaignBudgetType = GoogleAdsSearchads360V0ResourcesCampaignBudget::class;
  protected $campaignBudgetDataType = '';
  protected $campaignCriterionType = GoogleAdsSearchads360V0ResourcesCampaignCriterion::class;
  protected $campaignCriterionDataType = '';
  protected $campaignLabelType = GoogleAdsSearchads360V0ResourcesCampaignLabel::class;
  protected $campaignLabelDataType = '';
  protected $conversionActionType = GoogleAdsSearchads360V0ResourcesConversionAction::class;
  protected $conversionActionDataType = '';
  protected $customColumnsType = GoogleAdsSearchads360V0CommonValue::class;
  protected $customColumnsDataType = 'array';
  protected $customerType = GoogleAdsSearchads360V0ResourcesCustomer::class;
  protected $customerDataType = '';
  protected $customerClientType = GoogleAdsSearchads360V0ResourcesCustomerClient::class;
  protected $customerClientDataType = '';
  protected $customerManagerLinkType = GoogleAdsSearchads360V0ResourcesCustomerManagerLink::class;
  protected $customerManagerLinkDataType = '';
  protected $dynamicSearchAdsSearchTermViewType = GoogleAdsSearchads360V0ResourcesDynamicSearchAdsSearchTermView::class;
  protected $dynamicSearchAdsSearchTermViewDataType = '';
  protected $genderViewType = GoogleAdsSearchads360V0ResourcesGenderView::class;
  protected $genderViewDataType = '';
  protected $keywordViewType = GoogleAdsSearchads360V0ResourcesKeywordView::class;
  protected $keywordViewDataType = '';
  protected $labelType = GoogleAdsSearchads360V0ResourcesLabel::class;
  protected $labelDataType = '';
  protected $locationViewType = GoogleAdsSearchads360V0ResourcesLocationView::class;
  protected $locationViewDataType = '';
  protected $metricsType = GoogleAdsSearchads360V0CommonMetrics::class;
  protected $metricsDataType = '';
  protected $productGroupViewType = GoogleAdsSearchads360V0ResourcesProductGroupView::class;
  protected $productGroupViewDataType = '';
  protected $segmentsType = GoogleAdsSearchads360V0CommonSegments::class;
  protected $segmentsDataType = '';
  protected $userListType = GoogleAdsSearchads360V0ResourcesUserList::class;
  protected $userListDataType = '';
  protected $webpageViewType = GoogleAdsSearchads360V0ResourcesWebpageView::class;
  protected $webpageViewDataType = '';

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
   * @param GoogleAdsSearchads360V0ResourcesAdGroupAd
   */
  public function setAdGroupAd(GoogleAdsSearchads360V0ResourcesAdGroupAd $adGroupAd)
  {
    $this->adGroupAd = $adGroupAd;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesAdGroupAd
   */
  public function getAdGroupAd()
  {
    return $this->adGroupAd;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesAdGroupAdLabel
   */
  public function setAdGroupAdLabel(GoogleAdsSearchads360V0ResourcesAdGroupAdLabel $adGroupAdLabel)
  {
    $this->adGroupAdLabel = $adGroupAdLabel;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesAdGroupAdLabel
   */
  public function getAdGroupAdLabel()
  {
    return $this->adGroupAdLabel;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesAdGroupAudienceView
   */
  public function setAdGroupAudienceView(GoogleAdsSearchads360V0ResourcesAdGroupAudienceView $adGroupAudienceView)
  {
    $this->adGroupAudienceView = $adGroupAudienceView;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesAdGroupAudienceView
   */
  public function getAdGroupAudienceView()
  {
    return $this->adGroupAudienceView;
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
   * @param GoogleAdsSearchads360V0ResourcesAdGroupCriterionLabel
   */
  public function setAdGroupCriterionLabel(GoogleAdsSearchads360V0ResourcesAdGroupCriterionLabel $adGroupCriterionLabel)
  {
    $this->adGroupCriterionLabel = $adGroupCriterionLabel;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesAdGroupCriterionLabel
   */
  public function getAdGroupCriterionLabel()
  {
    return $this->adGroupCriterionLabel;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesAdGroupLabel
   */
  public function setAdGroupLabel(GoogleAdsSearchads360V0ResourcesAdGroupLabel $adGroupLabel)
  {
    $this->adGroupLabel = $adGroupLabel;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesAdGroupLabel
   */
  public function getAdGroupLabel()
  {
    return $this->adGroupLabel;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesAgeRangeView
   */
  public function setAgeRangeView(GoogleAdsSearchads360V0ResourcesAgeRangeView $ageRangeView)
  {
    $this->ageRangeView = $ageRangeView;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesAgeRangeView
   */
  public function getAgeRangeView()
  {
    return $this->ageRangeView;
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
   * @param GoogleAdsSearchads360V0ResourcesCampaignAudienceView
   */
  public function setCampaignAudienceView(GoogleAdsSearchads360V0ResourcesCampaignAudienceView $campaignAudienceView)
  {
    $this->campaignAudienceView = $campaignAudienceView;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesCampaignAudienceView
   */
  public function getCampaignAudienceView()
  {
    return $this->campaignAudienceView;
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
   * @param GoogleAdsSearchads360V0ResourcesCampaignLabel
   */
  public function setCampaignLabel(GoogleAdsSearchads360V0ResourcesCampaignLabel $campaignLabel)
  {
    $this->campaignLabel = $campaignLabel;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesCampaignLabel
   */
  public function getCampaignLabel()
  {
    return $this->campaignLabel;
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
   * @param GoogleAdsSearchads360V0ResourcesDynamicSearchAdsSearchTermView
   */
  public function setDynamicSearchAdsSearchTermView(GoogleAdsSearchads360V0ResourcesDynamicSearchAdsSearchTermView $dynamicSearchAdsSearchTermView)
  {
    $this->dynamicSearchAdsSearchTermView = $dynamicSearchAdsSearchTermView;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesDynamicSearchAdsSearchTermView
   */
  public function getDynamicSearchAdsSearchTermView()
  {
    return $this->dynamicSearchAdsSearchTermView;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesGenderView
   */
  public function setGenderView(GoogleAdsSearchads360V0ResourcesGenderView $genderView)
  {
    $this->genderView = $genderView;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesGenderView
   */
  public function getGenderView()
  {
    return $this->genderView;
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
   * @param GoogleAdsSearchads360V0ResourcesLabel
   */
  public function setLabel(GoogleAdsSearchads360V0ResourcesLabel $label)
  {
    $this->label = $label;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesLabel
   */
  public function getLabel()
  {
    return $this->label;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesLocationView
   */
  public function setLocationView(GoogleAdsSearchads360V0ResourcesLocationView $locationView)
  {
    $this->locationView = $locationView;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesLocationView
   */
  public function getLocationView()
  {
    return $this->locationView;
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
  /**
   * @param GoogleAdsSearchads360V0ResourcesUserList
   */
  public function setUserList(GoogleAdsSearchads360V0ResourcesUserList $userList)
  {
    $this->userList = $userList;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesUserList
   */
  public function getUserList()
  {
    return $this->userList;
  }
  /**
   * @param GoogleAdsSearchads360V0ResourcesWebpageView
   */
  public function setWebpageView(GoogleAdsSearchads360V0ResourcesWebpageView $webpageView)
  {
    $this->webpageView = $webpageView;
  }
  /**
   * @return GoogleAdsSearchads360V0ResourcesWebpageView
   */
  public function getWebpageView()
  {
    return $this->webpageView;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleAdsSearchads360V0ServicesSearchAds360Row::class, 'Google_Service_SA360_GoogleAdsSearchads360V0ServicesSearchAds360Row');
