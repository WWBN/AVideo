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

class LearningGenaiRootCodeyTruncatorMetadata extends \Google\Model
{
  /**
   * @var int
   */
  public $cutoffIndex;
  /**
   * @var string
   */
  public $truncatedText;

  /**
   * @param int
   */
  public function setCutoffIndex($cutoffIndex)
  {
    $this->cutoffIndex = $cutoffIndex;
  }
  /**
   * @return int
   */
  public function getCutoffIndex()
  {
    return $this->cutoffIndex;
  }
  /**
   * @param string
   */
  public function setTruncatedText($truncatedText)
  {
    $this->truncatedText = $truncatedText;
  }
  /**
   * @return string
   */
  public function getTruncatedText()
  {
    return $this->truncatedText;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(LearningGenaiRootCodeyTruncatorMetadata::class, 'Google_Service_Aiplatform_LearningGenaiRootCodeyTruncatorMetadata');
