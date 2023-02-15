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

class GeostorePriceInfoSourceDetails extends \Google\Collection
{
  protected $collection_key = 'sourceData';
  protected $lastUpdateSourcesType = GeostorePriceInfoSourceDetailsSourceData::class;
  protected $lastUpdateSourcesDataType = 'array';
  protected $sourceDataType = GeostorePriceInfoSourceDetailsSourceData::class;
  protected $sourceDataDataType = 'array';

  /**
   * @param GeostorePriceInfoSourceDetailsSourceData[]
   */
  public function setLastUpdateSources($lastUpdateSources)
  {
    $this->lastUpdateSources = $lastUpdateSources;
  }
  /**
   * @return GeostorePriceInfoSourceDetailsSourceData[]
   */
  public function getLastUpdateSources()
  {
    return $this->lastUpdateSources;
  }
  /**
   * @param GeostorePriceInfoSourceDetailsSourceData[]
   */
  public function setSourceData($sourceData)
  {
    $this->sourceData = $sourceData;
  }
  /**
   * @return GeostorePriceInfoSourceDetailsSourceData[]
   */
  public function getSourceData()
  {
    return $this->sourceData;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GeostorePriceInfoSourceDetails::class, 'Google_Service_Contentwarehouse_GeostorePriceInfoSourceDetails');
