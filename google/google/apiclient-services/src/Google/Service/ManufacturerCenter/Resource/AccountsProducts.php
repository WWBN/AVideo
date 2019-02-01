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

/**
 * The "products" collection of methods.
 * Typical usage is:
 *  <code>
 *   $manufacturersService = new Google_Service_ManufacturerCenter(...);
 *   $products = $manufacturersService->products;
 *  </code>
 */
class Google_Service_ManufacturerCenter_Resource_AccountsProducts extends Google_Service_Resource
{
  /**
   * Gets the product from a Manufacturer Center account, including product
   * issues. (products.get)
   *
   * @param string $parent Parent ID in the format `accounts/{account_id}`.
   *
   * `account_id` - The ID of the Manufacturer Center account.
   * @param string $name Name in the format
   * `{target_country}:{content_language}:{product_id}`.
   *
   * `target_country`   - The target country of the product as a CLDR territory
   * code (for example, US).
   *
   * `content_language` - The content language of the product as a two-letter
   * ISO 639-1 language code (for example, en).
   *
   * `product_id`     -   The ID of the product. For more information, see
   * https://support.google.com/manufacturers/answer/6124116#id.
   * @param array $optParams Optional parameters.
   * @return Google_Service_ManufacturerCenter_Product
   */
  public function get($parent, $name, $optParams = array())
  {
    $params = array('parent' => $parent, 'name' => $name);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_ManufacturerCenter_Product");
  }
  /**
   * Lists all the products in a Manufacturer Center account.
   * (products.listAccountsProducts)
   *
   * @param string $parent Parent ID in the format `accounts/{account_id}`.
   *
   * `account_id` - The ID of the Manufacturer Center account.
   * @param array $optParams Optional parameters.
   *
   * @opt_param int pageSize Maximum number of product statuses to return in the
   * response, used for paging.
   * @opt_param string pageToken The token returned by the previous request.
   * @return Google_Service_ManufacturerCenter_ListProductsResponse
   */
  public function listAccountsProducts($parent, $optParams = array())
  {
    $params = array('parent' => $parent);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_ManufacturerCenter_ListProductsResponse");
  }
}
