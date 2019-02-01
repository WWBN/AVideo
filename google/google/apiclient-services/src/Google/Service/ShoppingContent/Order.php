<?php
/*
 * Copyright 2016 Google Inc.
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

class Google_Service_ShoppingContent_Order extends Google_Collection
{
  protected $collection_key = 'shipments';
  public $acknowledged;
  public $channelType;
  protected $customerType = 'Google_Service_ShoppingContent_OrderCustomer';
  protected $customerDataType = '';
  protected $deliveryDetailsType = 'Google_Service_ShoppingContent_OrderDeliveryDetails';
  protected $deliveryDetailsDataType = '';
  public $id;
  public $kind;
  protected $lineItemsType = 'Google_Service_ShoppingContent_OrderLineItem';
  protected $lineItemsDataType = 'array';
  public $merchantId;
  public $merchantOrderId;
  protected $netAmountType = 'Google_Service_ShoppingContent_Price';
  protected $netAmountDataType = '';
  protected $paymentMethodType = 'Google_Service_ShoppingContent_OrderPaymentMethod';
  protected $paymentMethodDataType = '';
  public $paymentStatus;
  public $placedDate;
  protected $promotionsType = 'Google_Service_ShoppingContent_OrderPromotion';
  protected $promotionsDataType = 'array';
  protected $refundsType = 'Google_Service_ShoppingContent_OrderRefund';
  protected $refundsDataType = 'array';
  protected $shipmentsType = 'Google_Service_ShoppingContent_OrderShipment';
  protected $shipmentsDataType = 'array';
  protected $shippingCostType = 'Google_Service_ShoppingContent_Price';
  protected $shippingCostDataType = '';
  protected $shippingCostTaxType = 'Google_Service_ShoppingContent_Price';
  protected $shippingCostTaxDataType = '';
  public $shippingOption;
  public $status;

  public function setAcknowledged($acknowledged)
  {
    $this->acknowledged = $acknowledged;
  }
  public function getAcknowledged()
  {
    return $this->acknowledged;
  }
  public function setChannelType($channelType)
  {
    $this->channelType = $channelType;
  }
  public function getChannelType()
  {
    return $this->channelType;
  }
  public function setCustomer(Google_Service_ShoppingContent_OrderCustomer $customer)
  {
    $this->customer = $customer;
  }
  public function getCustomer()
  {
    return $this->customer;
  }
  public function setDeliveryDetails(Google_Service_ShoppingContent_OrderDeliveryDetails $deliveryDetails)
  {
    $this->deliveryDetails = $deliveryDetails;
  }
  public function getDeliveryDetails()
  {
    return $this->deliveryDetails;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLineItems($lineItems)
  {
    $this->lineItems = $lineItems;
  }
  public function getLineItems()
  {
    return $this->lineItems;
  }
  public function setMerchantId($merchantId)
  {
    $this->merchantId = $merchantId;
  }
  public function getMerchantId()
  {
    return $this->merchantId;
  }
  public function setMerchantOrderId($merchantOrderId)
  {
    $this->merchantOrderId = $merchantOrderId;
  }
  public function getMerchantOrderId()
  {
    return $this->merchantOrderId;
  }
  public function setNetAmount(Google_Service_ShoppingContent_Price $netAmount)
  {
    $this->netAmount = $netAmount;
  }
  public function getNetAmount()
  {
    return $this->netAmount;
  }
  public function setPaymentMethod(Google_Service_ShoppingContent_OrderPaymentMethod $paymentMethod)
  {
    $this->paymentMethod = $paymentMethod;
  }
  public function getPaymentMethod()
  {
    return $this->paymentMethod;
  }
  public function setPaymentStatus($paymentStatus)
  {
    $this->paymentStatus = $paymentStatus;
  }
  public function getPaymentStatus()
  {
    return $this->paymentStatus;
  }
  public function setPlacedDate($placedDate)
  {
    $this->placedDate = $placedDate;
  }
  public function getPlacedDate()
  {
    return $this->placedDate;
  }
  public function setPromotions($promotions)
  {
    $this->promotions = $promotions;
  }
  public function getPromotions()
  {
    return $this->promotions;
  }
  public function setRefunds($refunds)
  {
    $this->refunds = $refunds;
  }
  public function getRefunds()
  {
    return $this->refunds;
  }
  public function setShipments($shipments)
  {
    $this->shipments = $shipments;
  }
  public function getShipments()
  {
    return $this->shipments;
  }
  public function setShippingCost(Google_Service_ShoppingContent_Price $shippingCost)
  {
    $this->shippingCost = $shippingCost;
  }
  public function getShippingCost()
  {
    return $this->shippingCost;
  }
  public function setShippingCostTax(Google_Service_ShoppingContent_Price $shippingCostTax)
  {
    $this->shippingCostTax = $shippingCostTax;
  }
  public function getShippingCostTax()
  {
    return $this->shippingCostTax;
  }
  public function setShippingOption($shippingOption)
  {
    $this->shippingOption = $shippingOption;
  }
  public function getShippingOption()
  {
    return $this->shippingOption;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
}
