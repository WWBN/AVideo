<?php
/*
 * Copyright 2016 Google Inc.
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

class Google_Service_ToolResults_Image extends Google_Model
{
  protected $errorType = 'Google_Service_ToolResults_Status';
  protected $errorDataType = '';
  protected $sourceImageType = 'Google_Service_ToolResults_ToolOutputReference';
  protected $sourceImageDataType = '';
  public $stepId;
  protected $thumbnailType = 'Google_Service_ToolResults_Thumbnail';
  protected $thumbnailDataType = '';

  public function setError(Google_Service_ToolResults_Status $error)
  {
    $this->error = $error;
  }
  public function getError()
  {
    return $this->error;
  }
  public function setSourceImage(Google_Service_ToolResults_ToolOutputReference $sourceImage)
  {
    $this->sourceImage = $sourceImage;
  }
  public function getSourceImage()
  {
    return $this->sourceImage;
  }
  public function setStepId($stepId)
  {
    $this->stepId = $stepId;
  }
  public function getStepId()
  {
    return $this->stepId;
  }
  public function setThumbnail(Google_Service_ToolResults_Thumbnail $thumbnail)
  {
    $this->thumbnail = $thumbnail;
  }
  public function getThumbnail()
  {
    return $this->thumbnail;
  }
}
