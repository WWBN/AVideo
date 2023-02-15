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

class GoogleCloudContentwarehouseV1ClassifySplitAndExtractPipeline extends \Google\Collection
{
  protected $collection_key = 'extractProcessorInfos';
  protected $classifySplitProcessorInfosType = GoogleCloudContentwarehouseV1ProcessorInfo::class;
  protected $classifySplitProcessorInfosDataType = '';
  public $classifySplitProcessorInfos;
  protected $extractProcessorInfosType = GoogleCloudContentwarehouseV1ProcessorInfo::class;
  protected $extractProcessorInfosDataType = 'array';
  public $extractProcessorInfos;
  /**
   * @var string
   */
  public $inputPath;
  /**
   * @var string
   */
  public $processorResultsFolderPath;

  /**
   * @param GoogleCloudContentwarehouseV1ProcessorInfo
   */
  public function setClassifySplitProcessorInfos(GoogleCloudContentwarehouseV1ProcessorInfo $classifySplitProcessorInfos)
  {
    $this->classifySplitProcessorInfos = $classifySplitProcessorInfos;
  }
  /**
   * @return GoogleCloudContentwarehouseV1ProcessorInfo
   */
  public function getClassifySplitProcessorInfos()
  {
    return $this->classifySplitProcessorInfos;
  }
  /**
   * @param GoogleCloudContentwarehouseV1ProcessorInfo[]
   */
  public function setExtractProcessorInfos($extractProcessorInfos)
  {
    $this->extractProcessorInfos = $extractProcessorInfos;
  }
  /**
   * @return GoogleCloudContentwarehouseV1ProcessorInfo[]
   */
  public function getExtractProcessorInfos()
  {
    return $this->extractProcessorInfos;
  }
  /**
   * @param string
   */
  public function setInputPath($inputPath)
  {
    $this->inputPath = $inputPath;
  }
  /**
   * @return string
   */
  public function getInputPath()
  {
    return $this->inputPath;
  }
  /**
   * @param string
   */
  public function setProcessorResultsFolderPath($processorResultsFolderPath)
  {
    $this->processorResultsFolderPath = $processorResultsFolderPath;
  }
  /**
   * @return string
   */
  public function getProcessorResultsFolderPath()
  {
    return $this->processorResultsFolderPath;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudContentwarehouseV1ClassifySplitAndExtractPipeline::class, 'Google_Service_Contentwarehouse_GoogleCloudContentwarehouseV1ClassifySplitAndExtractPipeline');
