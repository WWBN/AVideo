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

class GoogleCloudAiplatformV1VertexRagStore extends \Google\Collection
{
  protected $collection_key = 'ragResources';
  /**
   * @var string[]
   */
  public $ragCorpora;
  protected $ragResourcesType = GoogleCloudAiplatformV1VertexRagStoreRagResource::class;
  protected $ragResourcesDataType = 'array';
  /**
   * @var int
   */
  public $similarityTopK;
  public $vectorDistanceThreshold;

  /**
   * @param string[]
   */
  public function setRagCorpora($ragCorpora)
  {
    $this->ragCorpora = $ragCorpora;
  }
  /**
   * @return string[]
   */
  public function getRagCorpora()
  {
    return $this->ragCorpora;
  }
  /**
   * @param GoogleCloudAiplatformV1VertexRagStoreRagResource[]
   */
  public function setRagResources($ragResources)
  {
    $this->ragResources = $ragResources;
  }
  /**
   * @return GoogleCloudAiplatformV1VertexRagStoreRagResource[]
   */
  public function getRagResources()
  {
    return $this->ragResources;
  }
  /**
   * @param int
   */
  public function setSimilarityTopK($similarityTopK)
  {
    $this->similarityTopK = $similarityTopK;
  }
  /**
   * @return int
   */
  public function getSimilarityTopK()
  {
    return $this->similarityTopK;
  }
  public function setVectorDistanceThreshold($vectorDistanceThreshold)
  {
    $this->vectorDistanceThreshold = $vectorDistanceThreshold;
  }
  public function getVectorDistanceThreshold()
  {
    return $this->vectorDistanceThreshold;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudAiplatformV1VertexRagStore::class, 'Google_Service_Aiplatform_GoogleCloudAiplatformV1VertexRagStore');
