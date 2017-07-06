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

class Google_Service_CloudFunctions_SourceRepository extends Google_Model
{
  public $branch;
  public $deployedRevision;
  public $repositoryUrl;
  public $revision;
  public $sourcePath;
  public $tag;

  public function setBranch($branch)
  {
    $this->branch = $branch;
  }
  public function getBranch()
  {
    return $this->branch;
  }
  public function setDeployedRevision($deployedRevision)
  {
    $this->deployedRevision = $deployedRevision;
  }
  public function getDeployedRevision()
  {
    return $this->deployedRevision;
  }
  public function setRepositoryUrl($repositoryUrl)
  {
    $this->repositoryUrl = $repositoryUrl;
  }
  public function getRepositoryUrl()
  {
    return $this->repositoryUrl;
  }
  public function setRevision($revision)
  {
    $this->revision = $revision;
  }
  public function getRevision()
  {
    return $this->revision;
  }
  public function setSourcePath($sourcePath)
  {
    $this->sourcePath = $sourcePath;
  }
  public function getSourcePath()
  {
    return $this->sourcePath;
  }
  public function setTag($tag)
  {
    $this->tag = $tag;
  }
  public function getTag()
  {
    return $this->tag;
  }
}
