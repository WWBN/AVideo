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

class GoogleCloudContentwarehouseV1RunPipelineRequest extends \Google\Model
{
  protected $exportCdwPipelineType = GoogleCloudContentwarehouseV1ExportToCdwPipeline::class;
  protected $exportCdwPipelineDataType = '';
  public $exportCdwPipeline;
  protected $gcsIngestPipelineType = GoogleCloudContentwarehouseV1GcsIngestPipeline::class;
  protected $gcsIngestPipelineDataType = '';
  public $gcsIngestPipeline;
  protected $gcsIngestWithDocAiProcessorsPipelineType = GoogleCloudContentwarehouseV1GcsIngestWithDocAiProcessorsPipeline::class;
  protected $gcsIngestWithDocAiProcessorsPipelineDataType = '';
  public $gcsIngestWithDocAiProcessorsPipeline;

  /**
   * @param GoogleCloudContentwarehouseV1ExportToCdwPipeline
   */
  public function setExportCdwPipeline(GoogleCloudContentwarehouseV1ExportToCdwPipeline $exportCdwPipeline)
  {
    $this->exportCdwPipeline = $exportCdwPipeline;
  }
  /**
   * @return GoogleCloudContentwarehouseV1ExportToCdwPipeline
   */
  public function getExportCdwPipeline()
  {
    return $this->exportCdwPipeline;
  }
  /**
   * @param GoogleCloudContentwarehouseV1GcsIngestPipeline
   */
  public function setGcsIngestPipeline(GoogleCloudContentwarehouseV1GcsIngestPipeline $gcsIngestPipeline)
  {
    $this->gcsIngestPipeline = $gcsIngestPipeline;
  }
  /**
   * @return GoogleCloudContentwarehouseV1GcsIngestPipeline
   */
  public function getGcsIngestPipeline()
  {
    return $this->gcsIngestPipeline;
  }
  /**
   * @param GoogleCloudContentwarehouseV1GcsIngestWithDocAiProcessorsPipeline
   */
  public function setGcsIngestWithDocAiProcessorsPipeline(GoogleCloudContentwarehouseV1GcsIngestWithDocAiProcessorsPipeline $gcsIngestWithDocAiProcessorsPipeline)
  {
    $this->gcsIngestWithDocAiProcessorsPipeline = $gcsIngestWithDocAiProcessorsPipeline;
  }
  /**
   * @return GoogleCloudContentwarehouseV1GcsIngestWithDocAiProcessorsPipeline
   */
  public function getGcsIngestWithDocAiProcessorsPipeline()
  {
    return $this->gcsIngestWithDocAiProcessorsPipeline;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudContentwarehouseV1RunPipelineRequest::class, 'Google_Service_Contentwarehouse_GoogleCloudContentwarehouseV1RunPipelineRequest');
