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

class QualityCalypsoAppsUniversalAuLiveOpEvent extends \Google\Model
{
  /**
   * @var string
   */
  public $endTimestampMillis;
  /**
   * @var string
   */
  public $previewTimestampMillis;
  /**
   * @var string
   */
  public $startTimestampMillis;

  /**
   * @param string
   */
  public function setEndTimestampMillis($endTimestampMillis)
  {
    $this->endTimestampMillis = $endTimestampMillis;
  }
  /**
   * @return string
   */
  public function getEndTimestampMillis()
  {
    return $this->endTimestampMillis;
  }
  /**
   * @param string
   */
  public function setPreviewTimestampMillis($previewTimestampMillis)
  {
    $this->previewTimestampMillis = $previewTimestampMillis;
  }
  /**
   * @return string
   */
  public function getPreviewTimestampMillis()
  {
    return $this->previewTimestampMillis;
  }
  /**
   * @param string
   */
  public function setStartTimestampMillis($startTimestampMillis)
  {
    $this->startTimestampMillis = $startTimestampMillis;
  }
  /**
   * @return string
   */
  public function getStartTimestampMillis()
  {
    return $this->startTimestampMillis;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(QualityCalypsoAppsUniversalAuLiveOpEvent::class, 'Google_Service_Contentwarehouse_QualityCalypsoAppsUniversalAuLiveOpEvent');
