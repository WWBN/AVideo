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

class Google_Service_Classroom_Attachment extends Google_Model
{
  protected $driveFileType = 'Google_Service_Classroom_DriveFile';
  protected $driveFileDataType = '';
  protected $formType = 'Google_Service_Classroom_Form';
  protected $formDataType = '';
  protected $linkType = 'Google_Service_Classroom_Link';
  protected $linkDataType = '';
  protected $youTubeVideoType = 'Google_Service_Classroom_YouTubeVideo';
  protected $youTubeVideoDataType = '';

  public function setDriveFile(Google_Service_Classroom_DriveFile $driveFile)
  {
    $this->driveFile = $driveFile;
  }
  public function getDriveFile()
  {
    return $this->driveFile;
  }
  public function setForm(Google_Service_Classroom_Form $form)
  {
    $this->form = $form;
  }
  public function getForm()
  {
    return $this->form;
  }
  public function setLink(Google_Service_Classroom_Link $link)
  {
    $this->link = $link;
  }
  public function getLink()
  {
    return $this->link;
  }
  public function setYouTubeVideo(Google_Service_Classroom_YouTubeVideo $youTubeVideo)
  {
    $this->youTubeVideo = $youTubeVideo;
  }
  public function getYouTubeVideo()
  {
    return $this->youTubeVideo;
  }
}
