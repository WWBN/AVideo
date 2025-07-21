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

class GoogleCloudAiplatformV1GenerateVideoResponse extends \Google\Collection
{
  protected $collection_key = 'videos';
  /**
   * @var string[]
   */
  public $generatedSamples;
  /**
   * @var int
   */
  public $raiMediaFilteredCount;
  /**
   * @var string[]
   */
  public $raiMediaFilteredReasons;
  protected $videosType = GoogleCloudAiplatformV1GenerateVideoResponseVideo::class;
  protected $videosDataType = 'array';

  /**
   * @param string[]
   */
  public function setGeneratedSamples($generatedSamples)
  {
    $this->generatedSamples = $generatedSamples;
  }
  /**
   * @return string[]
   */
  public function getGeneratedSamples()
  {
    return $this->generatedSamples;
  }
  /**
   * @param int
   */
  public function setRaiMediaFilteredCount($raiMediaFilteredCount)
  {
    $this->raiMediaFilteredCount = $raiMediaFilteredCount;
  }
  /**
   * @return int
   */
  public function getRaiMediaFilteredCount()
  {
    return $this->raiMediaFilteredCount;
  }
  /**
   * @param string[]
   */
  public function setRaiMediaFilteredReasons($raiMediaFilteredReasons)
  {
    $this->raiMediaFilteredReasons = $raiMediaFilteredReasons;
  }
  /**
   * @return string[]
   */
  public function getRaiMediaFilteredReasons()
  {
    return $this->raiMediaFilteredReasons;
  }
  /**
   * @param GoogleCloudAiplatformV1GenerateVideoResponseVideo[]
   */
  public function setVideos($videos)
  {
    $this->videos = $videos;
  }
  /**
   * @return GoogleCloudAiplatformV1GenerateVideoResponseVideo[]
   */
  public function getVideos()
  {
    return $this->videos;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudAiplatformV1GenerateVideoResponse::class, 'Google_Service_Aiplatform_GoogleCloudAiplatformV1GenerateVideoResponse');
