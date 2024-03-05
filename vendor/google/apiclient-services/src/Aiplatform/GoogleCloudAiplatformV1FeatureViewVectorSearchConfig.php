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

namespace Google\Service\Aiplatform;

class GoogleCloudAiplatformV1FeatureViewVectorSearchConfig extends \Google\Collection
{
  protected $collection_key = 'filterColumns';
  protected $bruteForceConfigType = GoogleCloudAiplatformV1FeatureViewVectorSearchConfigBruteForceConfig::class;
  protected $bruteForceConfigDataType = '';
  /**
   * @var string
   */
  public $crowdingColumn;
  /**
   * @var string
   */
  public $distanceMeasureType;
  /**
   * @var string
   */
  public $embeddingColumn;
  /**
   * @var int
   */
  public $embeddingDimension;
  /**
   * @var string[]
   */
  public $filterColumns;
  protected $treeAhConfigType = GoogleCloudAiplatformV1FeatureViewVectorSearchConfigTreeAHConfig::class;
  protected $treeAhConfigDataType = '';

  /**
   * @param GoogleCloudAiplatformV1FeatureViewVectorSearchConfigBruteForceConfig
   */
  public function setBruteForceConfig(GoogleCloudAiplatformV1FeatureViewVectorSearchConfigBruteForceConfig $bruteForceConfig)
  {
    $this->bruteForceConfig = $bruteForceConfig;
  }
  /**
   * @return GoogleCloudAiplatformV1FeatureViewVectorSearchConfigBruteForceConfig
   */
  public function getBruteForceConfig()
  {
    return $this->bruteForceConfig;
  }
  /**
   * @param string
   */
  public function setCrowdingColumn($crowdingColumn)
  {
    $this->crowdingColumn = $crowdingColumn;
  }
  /**
   * @return string
   */
  public function getCrowdingColumn()
  {
    return $this->crowdingColumn;
  }
  /**
   * @param string
   */
  public function setDistanceMeasureType($distanceMeasureType)
  {
    $this->distanceMeasureType = $distanceMeasureType;
  }
  /**
   * @return string
   */
  public function getDistanceMeasureType()
  {
    return $this->distanceMeasureType;
  }
  /**
   * @param string
   */
  public function setEmbeddingColumn($embeddingColumn)
  {
    $this->embeddingColumn = $embeddingColumn;
  }
  /**
   * @return string
   */
  public function getEmbeddingColumn()
  {
    return $this->embeddingColumn;
  }
  /**
   * @param int
   */
  public function setEmbeddingDimension($embeddingDimension)
  {
    $this->embeddingDimension = $embeddingDimension;
  }
  /**
   * @return int
   */
  public function getEmbeddingDimension()
  {
    return $this->embeddingDimension;
  }
  /**
   * @param string[]
   */
  public function setFilterColumns($filterColumns)
  {
    $this->filterColumns = $filterColumns;
  }
  /**
   * @return string[]
   */
  public function getFilterColumns()
  {
    return $this->filterColumns;
  }
  /**
   * @param GoogleCloudAiplatformV1FeatureViewVectorSearchConfigTreeAHConfig
   */
  public function setTreeAhConfig(GoogleCloudAiplatformV1FeatureViewVectorSearchConfigTreeAHConfig $treeAhConfig)
  {
    $this->treeAhConfig = $treeAhConfig;
  }
  /**
   * @return GoogleCloudAiplatformV1FeatureViewVectorSearchConfigTreeAHConfig
   */
  public function getTreeAhConfig()
  {
    return $this->treeAhConfig;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudAiplatformV1FeatureViewVectorSearchConfig::class, 'Google_Service_Aiplatform_GoogleCloudAiplatformV1FeatureViewVectorSearchConfig');
