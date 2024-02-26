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

class QualityTimebasedLastSignificantUpdateAdjustments extends \Google\Model
{
  /**
   * @var string
   */
  public $adjustmentSource;
  /**
   * @var bool
   */
  public $isUpperboundTimestampPrecise;
  /**
   * @var string
   */
  public $unboundedTimestampInSeconds;
  /**
   * @var string
   */
  public $unboundedTimestampSource;
  /**
   * @var string
   */
  public $upperboundTimestampInSeconds;

  /**
   * @param string
   */
  public function setAdjustmentSource($adjustmentSource)
  {
    $this->adjustmentSource = $adjustmentSource;
  }
  /**
   * @return string
   */
  public function getAdjustmentSource()
  {
    return $this->adjustmentSource;
  }
  /**
   * @param bool
   */
  public function setIsUpperboundTimestampPrecise($isUpperboundTimestampPrecise)
  {
    $this->isUpperboundTimestampPrecise = $isUpperboundTimestampPrecise;
  }
  /**
   * @return bool
   */
  public function getIsUpperboundTimestampPrecise()
  {
    return $this->isUpperboundTimestampPrecise;
  }
  /**
   * @param string
   */
  public function setUnboundedTimestampInSeconds($unboundedTimestampInSeconds)
  {
    $this->unboundedTimestampInSeconds = $unboundedTimestampInSeconds;
  }
  /**
   * @return string
   */
  public function getUnboundedTimestampInSeconds()
  {
    return $this->unboundedTimestampInSeconds;
  }
  /**
   * @param string
   */
  public function setUnboundedTimestampSource($unboundedTimestampSource)
  {
    $this->unboundedTimestampSource = $unboundedTimestampSource;
  }
  /**
   * @return string
   */
  public function getUnboundedTimestampSource()
  {
    return $this->unboundedTimestampSource;
  }
  /**
   * @param string
   */
  public function setUpperboundTimestampInSeconds($upperboundTimestampInSeconds)
  {
    $this->upperboundTimestampInSeconds = $upperboundTimestampInSeconds;
  }
  /**
   * @return string
   */
  public function getUpperboundTimestampInSeconds()
  {
    return $this->upperboundTimestampInSeconds;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(QualityTimebasedLastSignificantUpdateAdjustments::class, 'Google_Service_Contentwarehouse_QualityTimebasedLastSignificantUpdateAdjustments');
