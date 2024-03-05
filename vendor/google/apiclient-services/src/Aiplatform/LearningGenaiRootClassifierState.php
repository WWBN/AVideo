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

class LearningGenaiRootClassifierState extends \Google\Collection
{
  protected $collection_key = 'metricOutput';
  protected $dataProviderOutputType = LearningGenaiRootDataProviderOutput::class;
  protected $dataProviderOutputDataType = 'array';
  protected $metricOutputType = LearningGenaiRootMetricOutput::class;
  protected $metricOutputDataType = 'array';

  /**
   * @param LearningGenaiRootDataProviderOutput[]
   */
  public function setDataProviderOutput($dataProviderOutput)
  {
    $this->dataProviderOutput = $dataProviderOutput;
  }
  /**
   * @return LearningGenaiRootDataProviderOutput[]
   */
  public function getDataProviderOutput()
  {
    return $this->dataProviderOutput;
  }
  /**
   * @param LearningGenaiRootMetricOutput[]
   */
  public function setMetricOutput($metricOutput)
  {
    $this->metricOutput = $metricOutput;
  }
  /**
   * @return LearningGenaiRootMetricOutput[]
   */
  public function getMetricOutput()
  {
    return $this->metricOutput;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootClassifierState::class, 'Google_Service_Aiplatform_LearningGenaiRootClassifierState');
