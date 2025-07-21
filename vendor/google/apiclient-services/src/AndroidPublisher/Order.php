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

namespace Google\Service\AndroidPublisher;

class Order extends \Google\Collection
{
  protected $collection_key = 'lineItems';
  protected $buyerAddressType = BuyerAddress::class;
  protected $buyerAddressDataType = '';
  /**
   * @var string
   */
  public $createTime;
  protected $developerRevenueInBuyerCurrencyType = Money::class;
  protected $developerRevenueInBuyerCurrencyDataType = '';
  /**
   * @var string
   */
  public $lastEventTime;
  protected $lineItemsType = LineItem::class;
  protected $lineItemsDataType = 'array';
  protected $orderDetailsType = OrderDetails::class;
  protected $orderDetailsDataType = '';
  protected $orderHistoryType = OrderHistory::class;
  protected $orderHistoryDataType = '';
  /**
   * @var string
   */
  public $orderId;
  protected $pointsDetailsType = PointsDetails::class;
  protected $pointsDetailsDataType = '';
  /**
   * @var string
   */
  public $purchaseToken;
  /**
   * @var string
   */
  public $state;
  protected $taxType = Money::class;
  protected $taxDataType = '';
  protected $totalType = Money::class;
  protected $totalDataType = '';

  /**
   * @param BuyerAddress
   */
  public function setBuyerAddress(BuyerAddress $buyerAddress)
  {
    $this->buyerAddress = $buyerAddress;
  }
  /**
   * @return BuyerAddress
   */
  public function getBuyerAddress()
  {
    return $this->buyerAddress;
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
   * @param Money
   */
  public function setDeveloperRevenueInBuyerCurrency(Money $developerRevenueInBuyerCurrency)
  {
    $this->developerRevenueInBuyerCurrency = $developerRevenueInBuyerCurrency;
  }
  /**
   * @return Money
   */
  public function getDeveloperRevenueInBuyerCurrency()
  {
    return $this->developerRevenueInBuyerCurrency;
  }
  /**
   * @param string
   */
  public function setLastEventTime($lastEventTime)
  {
    $this->lastEventTime = $lastEventTime;
  }
  /**
   * @return string
   */
  public function getLastEventTime()
  {
    return $this->lastEventTime;
  }
  /**
   * @param LineItem[]
   */
  public function setLineItems($lineItems)
  {
    $this->lineItems = $lineItems;
  }
  /**
   * @return LineItem[]
   */
  public function getLineItems()
  {
    return $this->lineItems;
  }
  /**
   * @param OrderDetails
   */
  public function setOrderDetails(OrderDetails $orderDetails)
  {
    $this->orderDetails = $orderDetails;
  }
  /**
   * @return OrderDetails
   */
  public function getOrderDetails()
  {
    return $this->orderDetails;
  }
  /**
   * @param OrderHistory
   */
  public function setOrderHistory(OrderHistory $orderHistory)
  {
    $this->orderHistory = $orderHistory;
  }
  /**
   * @return OrderHistory
   */
  public function getOrderHistory()
  {
    return $this->orderHistory;
  }
  /**
   * @param string
   */
  public function setOrderId($orderId)
  {
    $this->orderId = $orderId;
  }
  /**
   * @return string
   */
  public function getOrderId()
  {
    return $this->orderId;
  }
  /**
   * @param PointsDetails
   */
  public function setPointsDetails(PointsDetails $pointsDetails)
  {
    $this->pointsDetails = $pointsDetails;
  }
  /**
   * @return PointsDetails
   */
  public function getPointsDetails()
  {
    return $this->pointsDetails;
  }
  /**
   * @param string
   */
  public function setPurchaseToken($purchaseToken)
  {
    $this->purchaseToken = $purchaseToken;
  }
  /**
   * @return string
   */
  public function getPurchaseToken()
  {
    return $this->purchaseToken;
  }
  /**
   * @param string
   */
  public function setState($state)
  {
    $this->state = $state;
  }
  /**
   * @return string
   */
  public function getState()
  {
    return $this->state;
  }
  /**
   * @param Money
   */
  public function setTax(Money $tax)
  {
    $this->tax = $tax;
  }
  /**
   * @return Money
   */
  public function getTax()
  {
    return $this->tax;
  }
  /**
   * @param Money
   */
  public function setTotal(Money $total)
  {
    $this->total = $total;
  }
  /**
   * @return Money
   */
  public function getTotal()
  {
    return $this->total;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(Order::class, 'Google_Service_AndroidPublisher_Order');
