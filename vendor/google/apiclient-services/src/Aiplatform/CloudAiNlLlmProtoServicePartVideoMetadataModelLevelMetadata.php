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

class CloudAiNlLlmProtoServicePartVideoMetadataModelLevelMetadata extends \Google\Model
{
  /**
   * @var float
   */
  public $fps;
  /**
   * @var int
   */
  public $numFrames;

  /**
   * @param float
   */
  public function setFps($fps)
  {
    $this->fps = $fps;
  }
  /**
   * @return float
   */
  public function getFps()
  {
    return $this->fps;
  }
  /**
   * @param int
   */
  public function setNumFrames($numFrames)
  {
    $this->numFrames = $numFrames;
  }
  /**
   * @return int
   */
  public function getNumFrames()
  {
    return $this->numFrames;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CloudAiNlLlmProtoServicePartVideoMetadataModelLevelMetadata::class, 'Google_Service_Aiplatform_CloudAiNlLlmProtoServicePartVideoMetadataModelLevelMetadata');
