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

namespace Google\Service\DisplayVideo;

class ContentThemeAssignedTargetingOptionDetails extends \Google\Model
{
  /**
   * @var string
   */
  public $contentTheme;
  /**
   * @var string
   */
  public $excludedContentTheme;
  /**
   * @var string
   */
  public $excludedTargetingOptionId;

  /**
   * @param string
   */
  public function setContentTheme($contentTheme)
  {
    $this->contentTheme = $contentTheme;
  }
  /**
   * @return string
   */
  public function getContentTheme()
  {
    return $this->contentTheme;
  }
  /**
   * @param string
   */
  public function setExcludedContentTheme($excludedContentTheme)
  {
    $this->excludedContentTheme = $excludedContentTheme;
  }
  /**
   * @return string
   */
  public function getExcludedContentTheme()
  {
    return $this->excludedContentTheme;
  }
  /**
   * @param string
   */
  public function setExcludedTargetingOptionId($excludedTargetingOptionId)
  {
    $this->excludedTargetingOptionId = $excludedTargetingOptionId;
  }
  /**
   * @return string
   */
  public function getExcludedTargetingOptionId()
  {
    return $this->excludedTargetingOptionId;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ContentThemeAssignedTargetingOptionDetails::class, 'Google_Service_DisplayVideo_ContentThemeAssignedTargetingOptionDetails');
