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

namespace Google\Service\CloudDataplex;

class GoogleCloudDataplexV1MetadataJobExportJobSpec extends \Google\Model
{
  /**
   * @var string
   */
  public $outputPath;
  protected $scopeType = GoogleCloudDataplexV1MetadataJobExportJobSpecExportJobScope::class;
  protected $scopeDataType = '';

  /**
   * @param string
   */
  public function setOutputPath($outputPath)
  {
    $this->outputPath = $outputPath;
  }
  /**
   * @return string
   */
  public function getOutputPath()
  {
    return $this->outputPath;
  }
  /**
   * @param GoogleCloudDataplexV1MetadataJobExportJobSpecExportJobScope
   */
  public function setScope(GoogleCloudDataplexV1MetadataJobExportJobSpecExportJobScope $scope)
  {
    $this->scope = $scope;
  }
  /**
   * @return GoogleCloudDataplexV1MetadataJobExportJobSpecExportJobScope
   */
  public function getScope()
  {
    return $this->scope;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudDataplexV1MetadataJobExportJobSpec::class, 'Google_Service_CloudDataplex_GoogleCloudDataplexV1MetadataJobExportJobSpec');
